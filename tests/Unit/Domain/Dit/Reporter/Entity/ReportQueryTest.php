<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Entity\ReportField;
use App\Domain\Dit\Reporter\Entity\ReportParam;
use App\Domain\Dit\Reporter\Entity\ReportQuery;

it('creates ReportQuery with default values', function (): void {
    $query = new ReportQuery();

    expect($query->sql)->toBe('');
    expect($query->caption)->toBe('');
    expect($query->keyField)->toBe('');
    expect($query->masterField)->toBe('');
    expect($query->detailField)->toBe('');
    expect($query->fields)->toBe([]);
    expect($query->params)->toBe([]);
    expect($query->sub)->toBe([]);
});

it('creates ReportQuery with basic values', function (): void {
    $query = new ReportQuery(
        sql: 'SELECT * FROM users',
        caption: 'User List',
        keyField: 'id',
        masterField: 'user_id',
        detailField: 'detail_id'
    );

    expect($query->sql)->toBe('SELECT * FROM users');
    expect($query->caption)->toBe('User List');
    expect($query->keyField)->toBe('id');
    expect($query->masterField)->toBe('user_id');
    expect($query->detailField)->toBe('detail_id');
});

it('creates ReportQuery with fields', function (): void {
    $fieldsData = [
        [
            'fieldName'    => 'id',
            'bandName'     => 'main',
            'displayLabel' => 'ID',
            'isCurrency'   => false,
        ],
        [
            'fieldName'    => 'name',
            'bandName'     => 'main',
            'displayLabel' => 'Name',
            'isCurrency'   => false,
        ],
    ];

    $query = new ReportQuery(fields: $fieldsData);

    expect($query->fields)->toHaveCount(2);
    expect($query->fields[0])->toBeInstanceOf(ReportField::class);
    expect($query->fields[0]->fieldName)->toBe('id');
    expect($query->fields[1]->fieldName)->toBe('name');
});

it('creates ReportQuery with params', function (): void {
    $paramsData = [
        [
            'name'     => 'user_id',
            'caption'  => 'User ID',
            'dataType' => 'ftInteger',
            'required' => true,
        ],
        [
            'name'     => 'start_date',
            'caption'  => 'Start Date',
            'dataType' => 'ftDate',
            'required' => false,
        ],
    ];

    $query = new ReportQuery(params: $paramsData);

    expect($query->params)->toHaveCount(2);
    expect($query->params[0])->toBeInstanceOf(ReportParam::class);
    expect($query->params[0]->name)->toBe('user_id');
    expect($query->params[1]->name)->toBe('start_date');
});

it('creates ReportQuery with sub queries', function (): void {
    $subData = [
        [
            'sql'     => 'SELECT * FROM orders',
            'caption' => 'Orders',
        ],
        [
            'sql'     => 'SELECT * FROM payments',
            'caption' => 'Payments',
        ],
    ];

    $query = new ReportQuery(sub: $subData);

    expect($query->sub)->toHaveCount(2);
    expect($query->sub[0])->toBeInstanceOf(ReportQuery::class);
    expect($query->sub[0]->sql)->toBe('SELECT * FROM orders');
    expect($query->sub[1]->sql)->toBe('SELECT * FROM payments');
});

it('filters out non-array values from fields, params, and sub', function (): void {
    $fieldsData = [
        [
            'fieldName' => 'id',
            'bandName'  => 'main',
        ],
        'invalid_field',
        [
            'fieldName' => 'name',
            'bandName'  => 'main',
        ],
    ];

    $paramsData = [
        [
            'name' => 'param1',
        ],
        null,
        [
            'name' => 'param2',
        ],
    ];

    $subData = [
        [
            'sql' => 'SELECT 1',
        ],
        123,
        [
            'sql' => 'SELECT 2',
        ],
    ];

    $query = new ReportQuery(
        fields: $fieldsData,
        params: $paramsData,
        sub: $subData
    );

    expect($query->fields)->toHaveCount(2);
    expect($query->params)->toHaveCount(2);
    expect($query->sub)->toHaveCount(2);
});

it('converts to array correctly', function (): void {
    $fieldsData = [
        [
            'fieldName'    => 'id',
            'bandName'     => 'main',
            'displayLabel' => 'ID',
            'isCurrency'   => false,
        ],
    ];

    $paramsData = [
        [
            'name'     => 'param1',
            'caption'  => 'Parameter 1',
            'required' => true,
        ],
        [
            'name'     => 'cur',
            'caption'  => 'Currency',
            'required' => false,
        ], // should be filtered out
        [
            'name'     => 'param2',
            'caption'  => 'Parameter 2',
            'required' => false,
        ],
    ];

    $query = new ReportQuery(
        keyField: 'id',
        masterField: 'master_id',
        detailField: 'detail_id',
        fields: $fieldsData,
        params: $paramsData
    );

    $result = $query->toArray();

    expect($result['keyField'])->toBe('id');
    expect($result['masterField'])->toBe('master_id');
    expect($result['detailField'])->toBe('detail_id');
    expect($result['fields'])->toHaveCount(1);
    expect($result['params'])->toHaveCount(2); // 'cur' should be filtered out
    expect($result['params'][0]['name'])->toBe('param1'); // required param should be first
    expect($result['params'][1]['name'])->toBe('param2');
});

it('sorts params by required status in toArray', function (): void {
    $paramsData = [
        [
            'name'     => 'optional1',
            'required' => false,
        ],
        [
            'name'     => 'required1',
            'required' => true,
        ],
        [
            'name'     => 'optional2',
            'required' => false,
        ],
        [
            'name'     => 'required2',
            'required' => true,
        ],
    ];

    $query = new ReportQuery(params: $paramsData);
    $result = $query->toArray();

    expect($result['params'][0]['name'])->toBe('required1');
    expect($result['params'][1]['name'])->toBe('required2');
    expect($result['params'][2]['name'])->toBe('optional1');
    expect($result['params'][3]['name'])->toBe('optional2');
});

it('filters out cur parameter in toArray', function (): void {
    $paramsData = [
        [
            'name'     => 'valid_param',
            'required' => true,
        ],
        [
            'name'     => 'cur',
            'required' => false,
        ],
        [
            'name'     => 'another_param',
            'required' => false,
        ],
    ];

    $query = new ReportQuery(params: $paramsData);
    $result = $query->toArray();

    expect($result['params'])->toHaveCount(2);
    expect(array_column($result['params'], 'name'))->not->toContain('cur');
});

it('creates complex nested ReportQuery', function (): void {
    $fieldsData = [
        [
            'fieldName'    => 'user_id',
            'displayLabel' => 'User ID',
        ],
        [
            'fieldName'    => 'username',
            'displayLabel' => 'Username',
        ],
    ];

    $paramsData = [
        [
            'name'     => 'date_from',
            'dataType' => 'ftDate',
            'required' => true,
        ],
        [
            'name'     => 'date_to',
            'dataType' => 'ftDate',
            'required' => true,
        ],
    ];

    $subData = [
        [
            'sql'     => 'SELECT * FROM user_orders WHERE user_id = :user_id',
            'caption' => 'User Orders',
            'fields'  => [[
                'fieldName'    => 'order_id',
                'displayLabel' => 'Order ID',
            ]],
        ],
    ];

    $query = new ReportQuery(
        sql: 'SELECT user_id, username FROM users WHERE created_date BETWEEN :date_from AND :date_to',
        caption: 'Users Report',
        keyField: 'user_id',
        fields: $fieldsData,
        params: $paramsData,
        sub: $subData
    );

    expect($query->sql)->toContain('SELECT user_id, username FROM users');
    expect($query->caption)->toBe('Users Report');
    expect($query->fields)->toHaveCount(2);
    expect($query->params)->toHaveCount(2);
    expect($query->sub)->toHaveCount(1);
    expect($query->sub[0]->sql)->toContain('SELECT * FROM user_orders');
});
