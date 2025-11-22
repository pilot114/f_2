<?php

declare(strict_types=1);

dataset('rpcRequests', [
    // single RPC request
    [
        '{"jsonrpc": "2.0", "method": "example.test.sum", "params": [2, 4], "id": 1}',
        [
            'jsonrpc' => '2.0',
            'result'  => 6,
            'id'      => 1,
        ],
    ],
    // batch RPC request
    [
        '[
            {"jsonrpc": "2.0", "method": "example.test.sum", "params": [1, 2], "id": 1},
            {"jsonrpc": "2.0", "method": "example.test.sum", "params": [4, 5], "id": 2}
        ]',
        [
            [
                'jsonrpc' => '2.0',
                'result'  => 3,
                'id'      => 1,
            ],
            [
                'jsonrpc' => '2.0',
                'result'  => 9,
                'id'      => 2,
            ],
        ],
    ],
    // invalid JSON request
    [
        '{',
        [
            'jsonrpc' => '2.0',
            'error'   => [
                'code'    => -32700,
                'message' => 'Невалидный JSON',
            ],
            'id' => -1,
        ],
    ],
    // invalid request
    [
        '{"jsonrpc": "2.0", "params": [1, 2], "id": 1}',
        [
            'jsonrpc' => '2.0',
            'error'   => [
                'code'    => -32600,
                'message' => 'Невалидный RPC запрос',
            ],
            'id' => -1,
        ],
    ],
    // method not found
    [
        '{"jsonrpc": "2.0", "method": "multiply", "params": [2, 3], "id": 1}',
        [
            'jsonrpc' => '2.0',
            'error'   => [
                'code'    => -32601,
                'message' => 'RPC метод не найден',
            ],
            'id' => 1,
        ],
    ],
]);
