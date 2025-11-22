<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Retriever;

use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use Database\Connection\ReadDatabaseInterface;
use Database\Schema\DbObject\DbObjectType;
use Database\Schema\EntityRetriever;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OracleArtefactRetriever implements ArtefactRetrieverInterface
{
    public function __construct(
        private ReadDatabaseInterface $conn,
        private EntityRetriever       $retriever,
    ) {
    }

    public function getDiffForLastDays(int $days = 1): array
    {
        // получение изменений, важных для обновления артефактов
        $sql = "SELECT DISTINCT
            d.OBJECT_TYPE,
            d.OWNER,
            d.OBJECT_NAME
        FROM tehno.DDL_HISTORY_2 d
        WHERE DDL_DATE >= TRUNC(SYSDATE - :days)
            AND d.OBJECT_TYPE IN ('PROCEDURE', 'TABLE', 'VIEW')
            AND d.DDL_TYPE IN ('CREATE', 'ALTER')
            AND d.OBJECT_NAME NOT LIKE '%#%'
            AND d.OBJECT_NAME NOT LIKE '%$%'
            AND d.OBJECT_NAME NOT LIKE '%SYS_JOURNAL%'
        ORDER BY d.OWNER, d.OBJECT_NAME
        ";

        return iterator_to_array($this->conn->query($sql, [
            'days' => $days,
        ]));
    }

    /**
     * Список всех артефактов определенного типа
     */
    public function getNameList(ArtefactType $type): array
    {
        return match ($type) {
            ArtefactType::TABLE     => $this->searchTableNames(),
            ArtefactType::PROCEDURE => $this->searchProcedureNames(),
            ArtefactType::VIEW      => $this->searchViewNames(),
            default                 => throw new BadRequestHttpException('Неподдерживаемый тип артефакта:' . $type->name),
        };
    }

    private function mapToDbObjectType(ArtefactType $type): DbObjectType
    {
        return match ($type) {
            ArtefactType::PROCEDURE => DbObjectType::Procedure,
            ArtefactType::FUNCTION  => DbObjectType::Function,
            ArtefactType::TABLE     => DbObjectType::Table,
            ArtefactType::VIEW      => DbObjectType::View,
            ArtefactType::TRIGGER   => DbObjectType::Trigger,
            ArtefactType::PACKAGE   => DbObjectType::Package,
            default                 => throw new BadRequestHttpException('Неподдерживаемый тип артефакта:' . $type->name),
        };
    }

    public function getChunk(array $fullNames, ArtefactType $type): array
    {
        return $this->retriever->getDbObjects($fullNames, $this->mapToDbObjectType($type));
    }

    public function get(string $fullName, ArtefactType $type): object
    {
        return $this->retriever->getDbObject($fullName, $this->mapToDbObjectType($type));
    }

    public function search(string $q, ?ArtefactType $type = null): array
    {
        if ($type instanceof ArtefactType) {
            $all = match ($type) {
                ArtefactType::TABLE     => $this->searchTableNames($q),
                ArtefactType::VIEW      => $this->searchViewNames($q),
                ArtefactType::PROCEDURE => $this->searchProcedureNames($q),
                ArtefactType::FUNCTION  => $this->searchFunctionNames(),
                default                 => throw new BadRequestHttpException('Неподдерживаемый тип артефакта:' . $type->name),
            };
            $all = array_map(static fn (string $x): array => [
                'name' => $x,
                'type' => $type,
            ], $all);
            return [$all, count($all)];
        }

        $procedures = $this->searchProcedureNames($q);
        $functions = $this->searchFunctionNames();
        $tables = $this->searchTableNames($q);
        $views = $this->searchViewNames($q);

        $all = [];
        foreach ($procedures as $procedure) {
            $all[] = [
                'name' => $procedure,
                'type' => ArtefactType::PROCEDURE,
            ];
        }
        foreach ($functions as $function) {
            $all[] = [
                'name' => $function,
                'type' => ArtefactType::FUNCTION,
            ];
        }
        foreach ($tables as $call) {
            $all[] = [
                'name' => $call,
                'type' => ArtefactType::TABLE,
            ];
        }
        foreach ($views as $call) {
            $all[] = [
                'name' => $call,
                'type' => ArtefactType::VIEW,
            ];
        }

        return [$all, count($all)];
    }

    private function searchProcedureNames(?string $q = null): array
    {
        $filter = $q !== null
            ? "and lower(OWNER || '.' || OBJECT_NAME || '.' || PROCEDURE_NAME) like ('%' || :q || '%')"
            : ''
        ;
        $params = $q !== null
            ? [
                'q' => mb_strtolower($q),
            ]
            : []
        ;

        $sql = <<<SQL
            SELECT p.OWNER, p.OBJECT_NAME, p.PROCEDURE_NAME
            FROM ALL_PROCEDURES p
            WHERE p.PROCEDURE_NAME IS NOT NULL
            AND p.object_name <> 'STANDARD' AND p.object_name NOT LIKE 'DBMS_%'
            AND p.OBJECT_TYPE = 'PACKAGE'
            $filter
            AND NOT EXISTS ( -- чтобы не попадали функции
                SELECT 1 FROM ALL_ARGUMENTS a
                WHERE a.OWNER = p.OWNER
                AND a.PACKAGE_NAME = p.OBJECT_NAME
                AND a.OBJECT_NAME = p.PROCEDURE_NAME
                AND a.ARGUMENT_NAME IS NULL
                AND a.DATA_LEVEL = 0
                AND a.POSITION = 0
            )
            GROUP BY OWNER, OBJECT_NAME, PROCEDURE_NAME
        SQL;

        $raw = $this->conn->query($sql , $params);
        $names = [];
        foreach ($raw as $item) {
            $names[] = sprintf('%s.%s.%s', $item['owner'], $item['object_name'], $item['procedure_name']);
        }
        sort($names);
        return $names;
    }

    private function searchFunctionNames(): array
    {
        return [];
    }

    private function searchTableNames(?string $q = null): array
    {
        $filter = $q !== null
            ? "WHERE lower(owner || '.' || table_name) like ('%' || :q || '%') GROUP BY owner, table_name"
            : ''
        ;
        $params = $q !== null
            ? [
                'q' => mb_strtolower($q),
            ]
            : []
        ;

        $sql = <<<SQL
            SELECT owner, table_name
            FROM SYS.ALL_TABLES
            $filter
        SQL;

        $raw = $this->conn->query($sql , $params);

        $names = [];
        foreach ($raw as $item) {
            $names[] = sprintf('%s.%s', $item['owner'], $item['table_name']);
        }
        sort($names);
        return $names;
    }

    private function searchViewNames(?string $q = null): array
    {
        $filter = $q !== null
            ? "and lower(owner || '.' || view_name) like ('%' || :q || '%')"
            : ''
        ;
        $params = $q !== null
            ? [
                'q' => mb_strtolower($q),
            ]
            : []
        ;

        $sql = <<<SQL
            SELECT owner, view_name
            FROM SYS.ALL_VIEWS
            WHERE owner NOT LIKE '%SYS%'
            $filter
            GROUP BY owner, view_name
        SQL;

        $raw = $this->conn->query($sql , $params);
        $names = [];
        foreach ($raw as $item) {
            $names[] = sprintf('%s.%s', $item['owner'], $item['view_name']);
        }
        sort($names);
        return $names;
    }
}
