<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\UseCase;

use App\Domain\Dit\Reporter\Entity\ReportParam;
use App\Domain\Dit\Reporter\Entity\ReportQuery;
use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\Connection\ReadDatabaseInterface;
use Database\Connection\WriteDatabaseInterface;
use Generator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ExecuteReportUseCase
{
    public ReportQuery $reportQuery;

    public const PREVIEW_LIMIT = 100;

    public function __construct(
        protected ReportQueryRepository $reportQueryRepository,
        protected ReadDatabaseInterface $connection,
        protected WriteDatabaseInterface $writeConnection,
        protected SecurityQueryRepository $access,
    ) {
    }

    public function executeReport(int $reportId, SecurityUser $currentUser, array $input = [], bool $allData = false): array
    {
        if (!$this->access->hasPermission($currentUser->id, 'rep_report', $reportId)) {
            throw new AccessDeniedHttpException("Нет прав на отчёт $reportId");
        }

        $report = $this->reportQueryRepository->getReport($reportId);

        ['databaseName' => $databaseName, 'queries' => $stack] = $report->getData();
        $this->configureOracleSession($databaseName, $currentUser);

        // ОПТИМИЗАЦИЯ: для больших экспортов включаем lazy loading
        if ($allData) {
            $this->connection->enableLazyLoading(batchSize: 1000);
        }

        // бёрем для выполнения только первый запрос

        /** @var ReportQuery $reportQuery */
        $reportQuery = array_shift($stack);
        if ($reportQuery->sub !== []) {
            $stack = array_merge($stack, $reportQuery->sub);
        }

        $this->reportQuery = $reportQuery;
        return $this->processQuery($reportQuery->sql, $reportQuery->params, $input, $allData);
    }

    private function configureOracleSession(string $databaseName, SecurityUser $currentUser): void
    {
        $result = $this->connection->query('select * from reporter.zz_databases where name=:name', [
            'name' => $databaseName,
        ])->current();

        $isNeedAuthorize = $result['need_authorize'] ?? false;
        if ($isNeedAuthorize) {
            $this->connection->procedure('reporter.preporter.authorize', [
                'id' => $currentUser->id,
            ]);
        }

        $sql = "ALTER SESSION SET CURRENT_SCHEMA = " . strtoupper($result['php_name']);
        $this->writeConnection->command($sql);
    }

    /**
     * @param ReportParam[] $params
     * @return array{iterable<array<string, mixed>>, ?int}
     */
    private function processQuery(string $sql, array $params, array $input, bool $allData = false): array
    {
        if ($sql === '') {
            return [[], 0];
        }

        $sqlParams = [];
        $types = [];
        $isCursor = false;
        $resultField = 'o_result';

        foreach ($params as $param) {
            $name = $param->name;
            $types[$name] = $param->dataType;
            $value = $input[$name] ?? '';

            if ($param->dataType === 'ftCursor') {
                $isCursor = true;
                $resultField = $name;
                continue;
            }
            if ($param->dataType === 'ftArray') {
                $sqlReplaced = preg_replace("/(:$name)([^_])/", "'" . implode("','", explode(',', $value)) . '\'\2', $sql);
                $sql = $sqlReplaced ?? $sql;
                unset($param);
                unset($types[$name]);
                continue;
            }
            if ($param->dataType === 'ftDate') {
                $sqlReplaced = preg_replace("/(:$name)([^_])/", "TO_DATE(:$name, 'DD.MM.YYYY')$2", $sql);
                $sql = $sqlReplaced ?? $sql;
                $sqlParams[$name] = $value;
                continue;
            }
            $sqlParams[$name] = $value;
        }

        if ($allData) {
            $iterator = $isCursor
                ? $this->procedureIterator($sql, $resultField, $sqlParams, $types)
                : $this->connection->query($sql, $sqlParams)
            ;
            return [
                $iterator,
                null,
            ];
        }

        if ($isCursor) {
            // Для preview загружаем в память и берем первые 100 строк
            $items = $this->procedure($sql, $resultField, $sqlParams, $types);
            $total = count($items);
            $items = array_slice($items, 0, self::PREVIEW_LIMIT);
            return [$items, $total];
        }

        return $this->getWithLimit($sql, $sqlParams);
    }

    /**
     * @return array{array<array<string, mixed>>, int}
     */
    private function getWithLimit(string $sql, array $params): array
    {
        $sqlSlice = sprintf('%s OFFSET 0 ROWS FETCH NEXT %d ROWS ONLY', $sql, self::PREVIEW_LIMIT);
        $result = $this->connection->query($sqlSlice, $params);

        // удаляем комментарии
        $sqlCount = trim((string) preg_replace('!/\*.*?\*/!s', '', $sql));

        // заменяем SELECT fields на SELECT COUNT, учитывая вложенные подзапросы
        $sqlTotal = $this->replaceSelectWithCount($sqlCount);

        $total = $this->connection->query($sqlTotal, $params);

        // запросы с group могут вернуть несколько count
        $totalCount = (int) array_sum(array_column(iterator_to_array($total), 'count'));

        return [iterator_to_array($result), $totalCount];
    }

    private function replaceSelectWithCount(string $sql): string
    {
        $depth = 0;
        $length = strlen($sql);
        $selectPos = -1;
        $fromPos = -1;

        for ($i = 0; $i < $length; $i++) {
            // пропускаем строковые литералы
            if ($sql[$i] === "'" || $sql[$i] === '"') {
                $quote = $sql[$i];
                $i++;
                while ($i < $length && $sql[$i] !== $quote) {
                    if ($sql[$i] === '\\') {
                        $i++; // пропускаем экранированный символ
                    }
                    $i++;
                }
                continue;
            }

            if ($sql[$i] === '(') {
                $depth++;
            } elseif ($sql[$i] === ')') {
                $depth--;
            }

            // ищем SELECT и FROM только на уровне 0 (основной запрос)
            if ($depth === 0) {
                if ($selectPos === -1 && preg_match('/^SELECT\s+/is', substr($sql, $i))) {
                    $selectPos = $i;
                }

                if ($selectPos !== -1 && preg_match('/^\s+FROM\s+/is', substr($sql, $i))) {
                    $fromPos = $i;
                    break;
                }
            }
        }

        if ($selectPos !== -1 && $fromPos !== -1) {
            return substr($sql, 0, $selectPos) . 'SELECT COUNT(1) count ' . substr($sql, $fromPos);
        }

        // если не нашли, используем старый метод как fallback
        return (string) preg_replace('/^SELECT\s+.*?\s+FROM\s+/is', 'SELECT COUNT(1) count FROM ', $sql);
    }

    private function procedure(string $sql, string $resultField, array $params = [], array $types = []): array
    {
        $convertTypes = [];
        foreach ($types as $name => &$type) {
            $params[$name] ??= null;
            $convertTypes[$name] = match ($type) {
                'ftCursor' => [ParamMode::OUT, ParamType::CURSOR],
                default    => [ParamMode::IN, ParamType::STRING],
            };
        }

        return $this->connection->queryOutCursor($sql, $resultField, $params, $convertTypes);
    }

    /**
     * Выполняет процедуру с курсором и возвращает генератор для потоковой обработки
     * ОПТИМИЗАЦИЯ: не загружает все данные в память сразу
     *
     * @return Generator<int, array>
     */
    private function procedureIterator(string $sql, string $resultField, array $params = [], array $types = []): Generator
    {
        // К сожалению, queryOutCursor не поддерживает потоковую обработку
        // Приходится загружать все данные, но хотя бы возвращаем как генератор
        // для совместимости с потоковой записью Excel
        $items = $this->procedure($sql, $resultField, $params, $types);

        foreach ($items as $item) {
            yield $item;
        }
    }
}
