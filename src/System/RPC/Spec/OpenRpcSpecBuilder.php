<?php

declare(strict_types=1);

namespace App\System\RPC\Spec;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use Generator;
use PSX\OpenAPI\{Info, License, Schemas, Server, Tag};
use PSX\OpenRPC\{Components, ContentDescriptor, Error, ExampleObject, ExamplePairingObject, Method, OpenRPC};

class OpenRpcSpecBuilder implements SpecBuilderInterface
{
    protected OpenRPC $openRPC;

    public function __construct(
        /** @var Generator<string, RpcMethod> */
        private readonly Generator $methods,
        private readonly Schemas $schemas,
        private readonly string $env,
    ) {
        $this->openRPC = new OpenRPC();
    }

    public function build(): OpenRPC
    {
        $preparedMethods = [];
        foreach ($this->methods as $method) {
            if (!$method instanceof RpcMethod) {
                continue;
            }

            $preparedMethods[] = $this->buildMethod($method);
        }

        $this->openRPC->setOpenrpc('1.3.2');
        $this->openRPC->setInfo($this->buildInfo());
        $this->openRPC->setServers($this->buildServers());
        $this->openRPC->setMethods($preparedMethods);

        $components = new Components();
        $components->setSchemas($this->schemas);
        $this->openRPC->setComponents($components);

        return $this->openRPC;
    }

    protected function setSchema(string $name, array $schema): void
    {
        $this->schemas->put($name, $schema);
    }

    protected function buildMethod(RpcMethod $rpc): Method
    {
        $method = new Method();

        $method->setName($rpc->name);
        $method->setSummary($rpc->summary);
        $method->setParamStructure('by-name');

        if ($rpc->description !== null) {
            $method->setDescription($rpc->description);
        }

        if ($rpc->isDeprecated) {
            $method->setDeprecated(true);
        }

        $tags = array_map(function ($x): Tag {
            $tag = new Tag();
            $tag->setName($x);
            return $tag;
        }, $rpc->tags);
        $method->setTags($tags);

        if ($rpc->examples) {
            $this->prepareExamples($method, $rpc->examples);
        }
        $this->prepareParams($method, $rpc);
        $this->prepareResult($method, $rpc);

        $errors = [];
        foreach ($rpc->errors as $code => $message) {
            $error = new Error();
            $error->setCode($code);
            $error->setMessage($message);
            $errors[] = $error;
        }
        if ($errors !== []) {
            $method->setErrors($errors);
        }

        return $method;
    }

    protected function prepareParams(Method $method, RpcMethod $rpc): void
    {
        $params = [];
        foreach ($rpc->params as $name => $paramAttr) {
            $param = new ContentDescriptor();
            $param->setName($name);
            $param->setSummary($paramAttr->summary);
            $param->setRequired($paramAttr->required);
            if ($paramAttr->deprecated) {
                $param->setDeprecated(true);
            }
            if ($paramAttr->schema) {
                if ($paramAttr->schemaName) {
                    $schemaName = str_replace('\\', '_', $paramAttr->schemaName);
                    $this->setSchema($schemaName, $paramAttr->schema);
                    $param->setSchema((object) [
                        '$ref' => "#/components/schemas/$schemaName",
                    ]);
                } else {
                    $param->setSchema($paramAttr->schema);
                }
            }
            $params[] = $param;
        }
        $method->setParams($params);
    }

    protected function prepareResult(Method $method, RpcMethod $rpc): void
    {
        $result = new ContentDescriptor();

        $result->setName('result');

        if ($resultSchema = $rpc->resultSchema) {
            if ($rpc->resultSchemaName !== null) {
                if ($rpc->genericTypeSchemaName !== null && $rpc->resultSchemaName === FindResponse::class) {
                    $schemaName = str_replace('\\', '_', $rpc->genericTypeSchemaName);
                    $this->setSchema($schemaName, $rpc->genericTypeSchema);
                    $resultSchema['properties']['items'] = [
                        '$ref' => "#/components/schemas/$schemaName",
                    ];
                    $result->setSchema($resultSchema);
                } else {
                    $schemaName = str_replace('\\', '_', $rpc->resultSchemaName);
                    $this->setSchema($schemaName, $resultSchema);
                    $result->setSchema([
                        '$ref' => "#/components/schemas/$schemaName",
                    ]);
                }
            } else {
                $result->setSchema($resultSchema);
            }
        }
        $method->setResult($result);
    }

    protected function prepareExamples(Method $method, array $examples): void
    {
        $result = [];
        foreach ($examples as $name => $exampleArr) {
            $example = new ExamplePairingObject();

            $example->setName(
                is_string($name)
                    ? $name
                    : "{$method->getName()}.$name"
            );

            if (isset($exampleArr['description'])) {
                $example->setDescription($exampleArr['description']);
            }
            if (isset($exampleArr['summary'])) {
                $example->setSummary($exampleArr['summary']);
            }

            $params = [];
            foreach ($exampleArr['params'] ?? [] as $paramName => $paramValue) {
                $item = new ExampleObject();
                $item->setName($paramName);
                if (is_string($paramValue)) {
                    $json = json_decode($paramValue);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $paramValue = $json;
                    }
                }
                $item->setValue($paramValue);
                $params[] = $item;
            }
            $example->setParams($params);

            $eo = new ExampleObject();
            $eo->setName('result');

            $eo->setValue(
                isset($exampleArr['result'])
                ? json_decode($exampleArr['result'], true)
                : []
            );

            $example->setResult($eo);

            $result[] = $example;
        }

        $method->setExamples($result);
    }

    /** @return Server[] */
    protected function buildServers(): array
    {
        $localServer = new Server();
        $localServer->setUrl('http://local.portal.com/api/v2/rpc');
        $betaServer = new Server();
        $betaServer->setUrl('https://beta-cp.siberianhealth.com/api/v2/rpc');
        $prodServer = new Server();
        $prodServer->setUrl('https://cp.siberianhealth.com/api/v2/rpc');
        return [$localServer, $betaServer, $prodServer];
    }

    protected function buildInfo(): Info
    {
        $info = new Info();
        $now = date('d-m-Y h:i:s');
        $info->setVersion("$this->env $now");

        $info->setTitle('CorPortal');

        $license = new License();
        $license->setName('proprietary');
        $info->setLicense($license);
        return $info;
    }
}
