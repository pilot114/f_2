<?php

declare(strict_types=1);

namespace App\System;

use Doctrine\DBAL\Exception\DriverException;
use Throwable;

/**
 * TODO: пока собираем обработку ошибок тут, потом вынесем этот класс в portal/database
 */
class OracleErrorHandler
{
    protected static array $oracleErrorMessages = [
        [
            'code'    => 1,
            'message' => 'Нарушено ограничение уникальности',
        ],
        [
            'code'    => 900,
            'message' => 'Невалидный SQL',
        ],
        [
            'code'    => 904,
            'message' => 'Несуществующий идентификатор',
        ],
        [
            'code'    => 907,
            'message' => 'Отсутствует правая скобка',
        ],
        [
            'code'    => 918,
            'message' => 'Поля в запросе определены неоднозначно',
        ],
        [
            'code'    => 936,
            'message' => 'Отсутствует часть запроса',
        ],
        [
            'code'    => 942,
            'message' => 'Таблица / представление не существует',
        ],
        [
            'code'    => 1034,
            'message' => 'База данных не отвечает',
        ],
        [
            'code'    => 4088,
            'message' => 'Ошибка выполнения триггера',
        ],
        [
            'code'    => 6512,
            'message' => 'Нарушено ограничение уникальности',
        ],
        [
            'code'    => 12899,
            'message' => 'Значение слишком большое',
        ],
        [
            'code'    => 2291,
            'message' => 'Нарушено ограничение внешнего ключа',
        ],
    ];

    protected static array $nativeErrorsRange = [
        'start'      => 1,
        'end'        => 19999,
        'title'      => 'Native oracle codes',
        'capability' => 19999,
    ];

    protected static array $raiseErrorsRange = [
        'start'      => 20000,
        'end'        => 20999,
        'title'      => 'User oracle codes',
        'capability' => 1000,
    ];

    public static function isRaised(int $code): bool
    {
        return $code >= self::$raiseErrorsRange['start'] && $code <= self::$raiseErrorsRange['end'];
    }

    public static function format(
        Throwable $exception,
        ?string $fullEntityName = null,
        ?string $sql = null,
        array $params = []
    ): ?string {
        $code = $exception->getCode();
        $rawMessage = $exception->getMessage();
        if ($sql === null && $exception instanceof DriverException) {
            $sql = $exception->getQuery()?->getSQL();
        }

        $contextMessage = match ($code) {
            1 => preg_match_all("#unique constraint \((.*)\)#", $rawMessage, $matches)
                ? sprintf('%s в %s', $matches[1][0], $fullEntityName)
                : null,
            900, 936, 907, 918 => $sql,
            904 => preg_match_all("#ORA-00904: \"(.*)\":#", $rawMessage, $matches)
            ? $matches[1][0] . ", sql: $sql"
            : null,
            942  => $fullEntityName ?: null,
            1843 => sprintf('В запрос передан невалидный месяц: "%s", параметры: %s', $sql, json_encode($params)),
            4088 => preg_match_all("#execution of trigger '(.*)'#", $rawMessage, $matches)
                ? sprintf('%s при добавлении записи в %s', $matches[1][0], $fullEntityName)
                : null,
            2291    => self::formatForeignKeyError($rawMessage),
            20201   => self::formatRaisedError($rawMessage, $code),
            default => self::isRaised($code) ? self::formatRaisedError($rawMessage, $code) : null,
        };
        foreach (self::$oracleErrorMessages as $message) {
            if ($message['code'] === $code) {
                return "{$message['message']}: $contextMessage";
            }
        }
        return $contextMessage;
    }

    private static function formatForeignKeyError(string $rawMessage): string
    {
        if (preg_match("#ORA-02291: integrity constraint \((.*)\) violated.*\n.*ORA-06512: at \"(.*)\", line (\d+)#", $rawMessage, $matches)) {
            return sprintf('%s в %s на строке %d', $matches[1], $matches[2], $matches[3]);
        }

        if (preg_match("#ORA-02291: integrity constraint \((.*)\) violated#", $rawMessage, $matches)) {
            return $matches[1];
        }

        return $rawMessage;
    }

    private static function formatRaisedError(string $rawMessage, int $code): string
    {
        if (preg_match("#.*ORA-{$code}: (.*)\n.*ORA-06512: at \"(.*)\", line (\d+)#", $rawMessage, $matches)) {
            return sprintf('При выполнении запроса произошла ошибка в %s на строке %d: %s', $matches[2], $matches[3], $matches[1]);
        }

        if (preg_match("#.*ORA-{$code}: (.*)#", $rawMessage, $matches)) {
            return sprintf('При выполнении запроса произошла ошибка: %s', $matches[1]);
        }

        return 'ORA raised: ' . str_replace(sprintf('ORA-%s: ', $code), '', $rawMessage);
    }
}
