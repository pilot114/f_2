<?php

declare(strict_types=1);

namespace App\Domain\Portal\Dictionary\Controller;

use App\Common\Attribute\RpcMethod;

class GetErrorCodesController
{
    #[RpcMethod(
        'portal.dictionary.getErrorCodes',
        'Список известных ошибок',
    )]
    /**
     * @return array{
     *     ranges: array<int, array{start: int, end: int, title: string, capability: int}>,
     *     errors: array<int, array{code: int, message: string}>,
     *     total: int
     * }
     */
    public function __invoke(
    ): array {
        $rpcErrors = [
            [
                'code'    => -32700,
                'message' => 'Невалидный JSON',
            ],
            [
                'code'    => -32603,
                'message' => 'Внутренняя ошибка RPC сервера',
            ],
            [
                'code'    => -32602,
                'message' => 'Невалидные RPC параметры',
            ],
            [
                'code'    => -32601,
                'message' => 'RPC метод не найден',
            ],
            [
                'code'    => -32600,
                'message' => 'Невалидный RPC запрос',
            ],
        ];
        $httpErrors = [
            [
                'code'    => 400,
                'message' => 'Ошибка валидации',
            ],
            [
                'code'    => 404,
                'message' => 'Не найдено',
            ],
            [
                'code'    => 403,
                'message' => 'Доступ запрещён',
            ],
            [
                'code'    => 409,
                'message' => 'Конфликт',
            ],
        ];

        return [
            'ranges' => [
                [
                    'start'      => -32768,
                    'end'        => -32000,
                    'title'      => 'RPC error codes',
                    'capability' => 769,
                ],
                [
                    'start'      => 400,
                    'end'        => 599,
                    'title'      => 'HTTP error codes',
                    'capability' => 200,
                ],
                [
                    'start'      => 600,
                    'end'        => 999,
                    'title'      => 'Domain codes',
                    'capability' => 400,
                ],
            ],
            'errors' => [
                ...$rpcErrors,
                ...$httpErrors,
            ],
            'total' => count($rpcErrors) + count($httpErrors),
        ];
    }
}
