<?php

declare(strict_types=1);

namespace App\System\RPC\Spec;

use App\Common\Attribute\RpcMethod;
use Generator;
use Symfony\Component\Routing\RouterInterface;

class PostmanSpecBuilder implements SpecBuilderInterface
{
    protected string $domain = 'local.portal.com';

    protected array $url = [
        'raw'   => '{{domain}}/api/v2/rpc',
        'host'  => ['{{domain}}'],
        'path'  => ['api', 'v2', 'rpc'],
        'query' => [],
    ];

    public function __construct(
        /** @var Generator<string, RpcMethod> */
        private Generator $methods,
        private RouterInterface $router,
    ) {
    }

    public function build(): array
    {
        $spec = $this->buildBase();
        $spec['item'][] = $this->appendSpecEndpoint();

        // Добавляем роуты из #[Route]
        foreach ($this->router->getRouteCollection() as $name => $route) {
            $method = $route->getMethods()[0];
            $type = $method === 'GET' ? 'Q' : 'C';

            // TODO: get info by reflection?
            $controllerName = $route->getDefaults()['_controller'];

            $spec['item'][] = [
                'name'    => sprintf("[%s] %s", $type, $name),
                'request' => [
                    'method' => $method,
                    'header' => [],
                    'body'   => [
                        'mode'    => 'raw',
                        'raw'     => "// see $controllerName",
                        'options' => [
                            'raw' => [
                                'language' => 'json',
                            ],
                        ],
                    ],
                    'url' => '{{domain}}' . $route->getPath(),
                ],
                'response' => [],
            ];
        }

        /** @var RpcMethod $method */
        foreach ($this->methods as $method) {
            $type = $method->isQuery() ? 'Q' : 'C';

            $spec['item'][] = [
                'name'    => sprintf("[%s] %s\n%s", $type, $method->name, $method->summary),
                'request' => [
                    'method' => 'POST',
                    'header' => [],
                    'body'   => [
                        'mode'    => 'raw',
                        'raw'     => $this->buildRequestExample($method),
                        'options' => [
                            'raw' => [
                                'language' => 'json',
                            ],
                        ],
                    ],
                    'url' => $this->url,
                ],
                'response' => [],
            ];
        }

        return $spec;
    }

    protected function buildRequestExample(RpcMethod $method): string
    {
        $params = $method->examples[array_key_first($method->examples)]['params'] ?? [];

        // decode json's
        foreach ($params as &$param) {
            if (is_string($param)) {
                //check, if json -> replace to decoded value
                $json = json_decode($param);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $param = $json;
                }
            }
        }
        $request = [
            'jsonrpc' => '2.0',
            'method'  => $method->name,
            'params'  => $params,
            'id'      => 1,
        ];
        return json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '';
    }

    protected function appendSpecEndpoint(): array
    {
        return [
            'name'    => 'spec',
            'request' => [
                'method' => 'GET',
                'header' => [],
                'url'    => [
                    'raw'   => '{{domain}}/api/v2/rpc?specType=postman',
                    'host'  => ['{{domain}}'],
                    'path'  => ['api', 'v2', 'rpc'],
                    'query' => [
                        [
                            'key'   => 'specType',
                            'value' => 'postman',
                        ],
                    ],
                ],
            ],
            'response' => [],
        ];
    }

    protected function buildBase(): array
    {
        return [
            'info' => [
                '_postman_id'  => 'ffa79932-65ee-4e6e-8c1b-e3647094e057',
                'name'         => 'CP 2.0',
                'description'  => 'Generated from {{domain}}/api/v2/rpc?specType=postman',
                'schema'       => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                '_exporter_id' => '15054581',
            ],
            'auth' => [
                'type'   => 'bearer',
                'bearer' => [
                    [
                        'key'   => 'token',
                        'value' => '{{token}}',
                        'type'  => 'string',
                    ],
                ],
            ],
            'variable' => [
                [
                    'key'   => 'token',
                    'value' => '',
                    'type'  => 'string',
                ],
                [
                    'key'   => 'domain',
                    'value' => $this->domain,
                    'type'  => 'string',
                ],
            ],
            'item' => [],
        ];
    }
}
