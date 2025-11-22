<?php

declare(strict_types=1);

namespace App\Tests\Unit\System;

use App\System\OracleErrorHandler;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

uses(MockeryPHPUnitIntegration::class);

describe('format', function (): void {
    it('formats unique constraint violation error (code 1)', function (): void {
        $exception = new Exception('ORA-00001: unique constraint (SCHEMA.UK_TABLE_COLUMN) violated', 1);

        $result = OracleErrorHandler::format($exception, 'TEST.TABLE');

        expect($result)->toBe('Нарушено ограничение уникальности: SCHEMA.UK_TABLE_COLUMN в TEST.TABLE');
    });

    it('formats table not found error (code 942)', function (): void {
        $exception = new Exception('ORA-00942: table or view does not exist', 942);

        $result = OracleErrorHandler::format($exception, 'MISSING.TABLE');

        expect($result)->toBe('Таблица / представление не существует: MISSING.TABLE');
    });

    it('formats invalid SQL error (code 900)', function (): void {
        $exception = new Exception('ORA-00900: invalid SQL statement', 900);

        $result = OracleErrorHandler::format($exception, null, 'SELECT * FROM invalid_table');

        expect($result)->toBe('Невалидный SQL: SELECT * FROM invalid_table');
    });

    it('formats missing identifier error (code 904)', function (): void {
        $exception = new Exception('ORA-00904: "INVALID_COLUMN": invalid identifier', 904);

        $result = OracleErrorHandler::format($exception, null, 'SELECT invalid_column FROM table');

        expect($result)->toBe('Несуществующий идентификатор: INVALID_COLUMN, sql: SELECT invalid_column FROM table');
    });

    it('formats trigger execution error (code 4088)', function (): void {
        $exception = new Exception('ORA-04088: error during execution of trigger \'SCHEMA.TRIGGER_NAME\'', 4088);

        $result = OracleErrorHandler::format($exception, 'TEST.TABLE');

        expect($result)->toBe('Ошибка выполнения триггера: SCHEMA.TRIGGER_NAME при добавлении записи в TEST.TABLE');
    });

    it('formats value too large error (code 12899)', function (): void {
        $exception = new Exception('ORA-12899: value too large for column', 12899);

        $result = OracleErrorHandler::format($exception);

        expect($result)->toBe('Значение слишком большое: ');
    });

    it('formats user-raised errors (20000-20999)', function (): void {
        $exception = new Exception('ORA-20001: Custom business logic error occurred', 20001);

        $result = OracleErrorHandler::format($exception);

        expect($result)->toBe('При выполнении запроса произошла ошибка: Custom business logic error occurred');
    });

    it('formats user-raised errors (20000-20999) with file and line', function (): void {
        $exception = new Exception("An exception occurred while executing a query: ORA-20201: Нет корзины!\nORA-06512: at \"TEHNO.SHOP_CURSOR_ORDER\", line 2663", 20201);

        $result = OracleErrorHandler::format($exception);

        expect($result)->toBe('При выполнении запроса произошла ошибка в TEHNO.SHOP_CURSOR_ORDER на строке 2663: Нет корзины!');
    });

    it('extracts SQL from DriverException when not provided', function (): void {
        $exception = new Exception('ORA-00900: invalid SQL statement', 900);

        $result = OracleErrorHandler::format($exception, null, 'SELECT * FROM test_table');

        expect($result)->toBe('Невалидный SQL: SELECT * FROM test_table');
    });

    it('handles invalid month error (code 1843)', function (): void {
        $exception = new Exception('ORA-01843: not a valid month', 1843);

        $result = OracleErrorHandler::format($exception, null, 'INVALID_MONTH', [
            'param1' => 'value1',
        ]);

        expect($result)->toBe('В запрос передан невалидный месяц: "INVALID_MONTH", параметры: {"param1":"value1"}');
    });

    it('handles missing right parenthesis error (code 907)', function (): void {
        $exception = new Exception('ORA-00907: missing right parenthesis', 907);

        $result = OracleErrorHandler::format($exception, null, 'SELECT * FROM table WHERE (column = value');

        expect($result)->toBe('Отсутствует правая скобка: SELECT * FROM table WHERE (column = value');
    });

    it('handles ambiguous column definition error (code 918)', function (): void {
        $exception = new Exception('ORA-00918: column ambiguously defined', 918);

        $result = OracleErrorHandler::format($exception, null, 'SELECT id FROM table1, table2');

        expect($result)->toBe('Поля в запросе определены неоднозначно: SELECT id FROM table1, table2');
    });

    it('handles missing expression error (code 936)', function (): void {
        $exception = new Exception('ORA-00936: missing expression', 936);

        $result = OracleErrorHandler::format($exception, null, 'SELECT FROM table');

        expect($result)->toBe('Отсутствует часть запроса: SELECT FROM table');
    });

    it('returns null for unknown error codes', function (): void {
        $exception = new Exception('Unknown Oracle error', 99999);

        $result = OracleErrorHandler::format($exception);

        expect($result)->toBeNull();
    });

    it('returns context message for known error without predefined message', function (): void {
        $exception = new Exception('ORA-01034: ORACLE not available', 1034);

        $result = OracleErrorHandler::format($exception, 'TEST.TABLE');

        expect($result)->toBe('База данных не отвечает: ');
    });

    it('handles duplicate constraint violation (code 6512)', function (): void {
        $exception = new Exception('ORA-06512: at line 1', 6512);

        $result = OracleErrorHandler::format($exception);

        expect($result)->toBe('Нарушено ограничение уникальности: ');
    });

    it('formats foreign key constraint violation error (code 2291) with location', function (): void {
        $exception = new Exception("ORA-02291: integrity constraint (TEST.PRODUCT_OF_MONTH_FK) violated - parent key not found\nORA-06512: at \"TEHNO.SHOP_CURSOR_CALENDAR\", line 231", 2291);

        $result = OracleErrorHandler::format($exception);

        expect($result)->toBe('Нарушено ограничение внешнего ключа: TEST.PRODUCT_OF_MONTH_FK в TEHNO.SHOP_CURSOR_CALENDAR на строке 231');
    });

    it('formats foreign key constraint violation error (code 2291) without location', function (): void {
        $exception = new Exception('ORA-02291: integrity constraint (TEST.PRODUCT_OF_MONTH_FK) violated - parent key not found', 2291);

        $result = OracleErrorHandler::format($exception);

        expect($result)->toBe('Нарушено ограничение внешнего ключа: TEST.PRODUCT_OF_MONTH_FK');
    });

    it('formats foreign key constraint violation error (code 2291) fallback to raw message', function (): void {
        $exception = new Exception('Some unexpected foreign key error message', 2291);

        $result = OracleErrorHandler::format($exception);

        expect($result)->toBe('Нарушено ограничение внешнего ключа: Some unexpected foreign key error message');
    });

    it('formats user-raised error fallback when regex does not match', function (): void {
        $exception = new Exception('Some unexpected raised error format', 20201);

        $result = OracleErrorHandler::format($exception);

        expect($result)->toBe('ORA raised: Some unexpected raised error format');
    });
});

afterEach(function (): void {
    Mockery::close();
});
