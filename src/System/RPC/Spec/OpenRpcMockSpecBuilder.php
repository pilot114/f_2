<?php

declare(strict_types=1);

namespace App\System\RPC\Spec;

use App\System\RPC\Spec\Repository\MockSpecRepository;
use Exception;
use PSX\OpenAPI\Tag;
use PSX\OpenRPC\ContentDescriptor;
use PSX\OpenRPC\Error;
use PSX\OpenRPC\ExampleObject;
use PSX\OpenRPC\ExamplePairingObject;
use PSX\OpenRPC\Method;
use PSX\OpenRPC\OpenRPC;
use stdClass;

class OpenRpcMockSpecBuilder extends OpenRpcSpecBuilder
{
    public ?string $jsonMockSpec = null;
    public const MOCK_EXAMPLE_PREFIX = '[MOCK] ';
    public const MOCK_TAG_NAME = 'mock';
    private MockSpecRepository $repository;

    public function setMockRepository(MockSpecRepository $repository): void
    {
        $this->repository = $repository;
    }

    public function build(): OpenRPC
    {
        parent::build();

        $this->mergeMockSpec();

        return $this->openRPC;
    }

    public function saveMockSpec(OpenRPC $spec): bool
    {
        return $this->repository->saveMockSpec($spec);
    }

    public function addMock(string $methodName, mixed $exampleValue, ?array $params = null): bool
    {
        $openRPC = $this->build();
        $methods = $openRPC->getMethods() ?: [];

        $method = null;
        foreach ($methods as $existingMethod) {
            if ($existingMethod->getName() === $methodName) {
                $method = $existingMethod;
                break;
            }
        }

        if ($method === null) {
            $method = new Method();
            $method->setName($methodName);

            $summary = "Мок имплементация для " . $methodName;
            $method->setSummary($summary);

            $methods[] = $method;
            $openRPC->setMethods($methods);
        }

        if ($params) {
            $methodParams = $method->getParams() ?: [];
            $existingParamNames = [];

            foreach ($methodParams as $param) {
                $existingParamNames[] = $param->getName();
            }

            foreach ($params as $key => $value) {
                if (!in_array($key, $existingParamNames, true)) {
                    $contentDescriptor = new ContentDescriptor();
                    $contentDescriptor->setName($key);

                    $schema = $this->createSchemaForValue($value);
                    $contentDescriptor->setSchema($schema);

                    $type = gettype($value);
                    $contentDescriptor->setDescription("Параметр типа: $type");

                    $contentDescriptor->setRequired(true);

                    $methodParams[] = $contentDescriptor;
                }
            }

            $method->setParams($methodParams);
        }

        if (!$method->getResult()) {
            $resultDescriptor = new ContentDescriptor();
            $resultDescriptor->setName($methodName . ' Результат');

            $schema = $this->createSchemaForValue($exampleValue);
            $resultDescriptor->setSchema($schema);

            $type = gettype($exampleValue);
            $resultDescriptor->setDescription("Результат типа: $type");

            $method->setResult($resultDescriptor);
        }

        $examplePairing = new ExamplePairingObject();
        $examplePairing->setName($methodName);

        $description = $this->createExampleDescription($methodName, $params);
        $examplePairing->setDescription($description);

        $resultExample = new ExampleObject();
        $resultExample->setName($methodName . ' Результат');
        $resultExample->setValue($exampleValue);
        $examplePairing->setResult($resultExample);

        if ($params) {
            $paramExamples = [];
            foreach ($params as $key => $value) {
                $paramExample = new ExampleObject();
                $paramExample->setName($key);
                $paramExample->setValue($value);
                $paramExamples[] = $paramExample;
            }
            $examplePairing->setParams($paramExamples);
        }

        $examples = $method->getExamples() ?: [];

        $replaced = false;
        foreach ($examples as $i => $example) {
            if ($example->getName() === $methodName) {
                $examples[$i] = $examplePairing;
                $replaced = true;
                break;
            }
        }

        if (!$replaced) {
            $examples[] = $examplePairing;
        }

        $method->setExamples($examples);

        return $this->saveMockSpec($openRPC);
    }

    public function removeMock(string $methodName, ?array $params = null): bool
    {
        $openRPC = $this->build();
        $methods = $openRPC->getMethods() ?: [];

        $method = null;
        $methodIndex = null;
        foreach ($methods as $i => $existingMethod) {
            if ($existingMethod->getName() === $methodName) {
                $method = $existingMethod;
                $methodIndex = $i;
                break;
            }
        }

        if ($method === null) {
            throw new Exception("Метод для удаления не найден");
        }

        if ($params === null) {
            if (is_int($methodIndex)) {
                array_splice($methods, $methodIndex, 1);
                $openRPC->setMethods($methods);
            }
        } else {
            $examples = $method->getExamples() ?: [];

            $example = $this->findExampleByParams($examples, $params);
            if (!$example instanceof ExamplePairingObject) {
                throw new Exception("Пример для удаления не найден");
            }

            $exampleIndex = null;
            foreach ($examples as $i => $ex) {
                if ($ex->getName() === $example->getName()) {
                    $exampleIndex = $i;
                    break;
                }
            }

            if (is_int($exampleIndex)) {
                array_splice($examples, $exampleIndex, 1);
                $method->setExamples($examples);
            }
        }

        return $this->saveMockSpec($openRPC);
    }

    public function setMockJsonSpec(string $mockSpec): void
    {
        $this->jsonMockSpec = $mockSpec;
    }

    /**
     * Добавляет mock-методы в спеку.
     * Если метод уже есть, заменяет в нём examples
     */
    private function mergeMockSpec(): void
    {
        $mockSpec = $this->mockBuild();

        $originalMethods = $this->openRPC->getMethods() ?: [];
        $mockMethods = $mockSpec->getMethods() ?: [];

        if ($mockMethods === []) {
            return;
        }

        $methodMap = [];
        foreach ($originalMethods as $method) {
            $methodMap[$method->getName()] = $method;
        }

        foreach ($mockMethods as $mockMethod) {
            $methodName = $mockMethod->getName();
            $mockExamples = $mockMethod->getExamples() ?: [];

            if (isset($methodMap[$methodName])) {
                $existingMethod = $methodMap[$methodName];
                $existingExamples = $existingMethod->getExamples() ?: [];

                $existingExampleNames = [];
                foreach ($existingExamples as $example) {
                    $existingExampleNames[] = $example->getName();
                }

                foreach ($mockExamples as $mockExample) {
                    if (!in_array($mockExample->getName(), $existingExampleNames, true)) {
                        $existingExamples[] = $mockExample;
                    }
                }

                $existingMethod->setExamples($existingExamples);
            } else {
                $originalMethods[] = $mockMethod;
            }
        }

        $this->openRPC->setMethods($originalMethods);
    }

    private function mockBuild(): OpenRPC
    {
        $openRPC = new OpenRPC();

        $mockSpecRaw = $this->getMockSpec();

        if ($mockSpecRaw === '' || $mockSpecRaw === '0') {
            return $openRPC;
        }

        $mockSpecData = json_decode($mockSpecRaw, true);

        if (is_array($mockSpecData) && isset($mockSpecData['methods']) && is_array($mockSpecData['methods'])) {
            $methods = $this->buildMethods($mockSpecData['methods']);
            $openRPC->setMethods($methods);
        }

        return $openRPC;
    }

    private function buildMethods(array $methodsData): array
    {
        $methods = [];
        foreach ($methodsData as $methodData) {
            $method = new Method();
            $this->setMethodBasicInfo($method, $methodData);
            $this->addMockTag($method);
            $this->setMethodParams($method, $methodData);
            $this->setMethodResult($method, $methodData);
            $this->setMethodErrors($method, $methodData);
            $this->setMethodExamples($method, $methodData);
            $methods[] = $method;
        }
        return $methods;
    }

    private function setMethodBasicInfo(Method $method, array $methodData): void
    {
        if (isset($methodData['name']) && is_string($methodData['name'])) {
            $method->setName($methodData['name']);
        }

        if (isset($methodData['summary']) && is_string($methodData['summary'])) {
            $method->setSummary($methodData['summary']);
        }
    }

    private function getMockSpec(): string
    {
        if ($this->jsonMockSpec !== null) {
            return $this->jsonMockSpec;
        }

        if (!isset($this->repository)) {
            return '';
        }

        return $this->repository->getMockSpec();
    }

    private function createExampleDescription(string $methodName, ?array $params): string
    {
        if (!$params) {
            return "Стандартный пример для " . $methodName;
        }

        $paramSummary = $this->createParamSummary($params);
        return "Пример для " . $methodName . " с параметрами: " . $paramSummary;
    }

    private function createSchemaForValue(mixed $value): stdClass
    {
        $schema = new stdClass();

        if (is_null($value)) {
            $schema->type = 'null';
        } elseif (is_bool($value)) {
            $schema->type = 'boolean';
        } elseif (is_int($value)) {
            $schema->type = 'integer';
        } elseif (is_float($value)) {
            $schema->type = 'number';
        } elseif (is_string($value)) {
            $schema->type = 'string';
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $schema->format = 'date';
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
                $schema->format = 'date-time';
            } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $schema->format = 'email';
            } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                $schema->format = 'uri';
            }
        } elseif (is_array($value)) {
            if ($this->isAssociativeArray($value)) {
                $schema->type = 'object';
                $schema->properties = new stdClass();

                foreach ($value as $key => $item) {
                    $schema->properties->$key = $this->createSchemaForValue($item);
                }

                $schema->required = array_keys($value);
            } else {
                $schema->type = 'array';

                if ($value !== []) {
                    $schema->items = $this->createSchemaForValue($value[0]);
                } else {
                    $schema->items = new stdClass();
                    $schema->items->type = 'string';
                }
            }
        } else {
            $schema->type = 'object';
        }

        return $schema;
    }

    private function isAssociativeArray(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function createParamSummary(array $params): string
    {
        $parts = [];
        $counter = 0;

        foreach ($params as $key => $value) {
            if ($counter >= 2) {
                $parts[] = "...";
                break;
            }

            if (is_array($value)) {
                $parts[] = "$key=[объект]";
            } else {
                $parts[] = "$key=" . (is_scalar($value) ? $value : json_encode($value));
            }

            $counter++;
        }

        return implode(', ', $parts);
    }

    private function areParamsEqual(array $params1, array $params2): bool
    {
        if (count($params1) !== count($params2)) {
            return false;
        }

        foreach ($params1 as $key => $value) {
            if (!isset($params2[$key])) {
                return false;
            }

            if (is_array($value) && is_array($params2[$key])) {
                if (!$this->areParamsEqual($value, $params2[$key])) {
                    return false;
                }
            } elseif ($value !== $params2[$key]) {
                return false;
            }
        }

        return true;
    }

    private function findExampleByParams(array $examples, array $params): ?ExamplePairingObject
    {
        foreach ($examples as $example) {
            $exampleParams = $example->getParams();
            if (!$exampleParams) {
                continue;
            }

            $paramValues = [];
            foreach ($exampleParams as $exampleParam) {
                $paramValues[$exampleParam->getName()] = $exampleParam->getValue();
            }

            if ($this->areParamsEqual($paramValues, $params)) {
                return $example;
            }
        }

        return null;
    }

    private function addMockTag(Method $method): void
    {
        $mockTag = new Tag();
        $mockTag->setName(self::MOCK_TAG_NAME);
        $mockTag->setDescription('This is a mock method');
        $tags = $method->getTags() ?: [];
        $tags[] = $mockTag;
        $method->setTags($tags);
    }

    private function setMethodParams(Method $method, array $methodData): void
    {
        if (!isset($methodData['params']) || !is_array($methodData['params'])) {
            return;
        }

        $params = [];
        foreach ($methodData['params'] as $paramData) {
            $content = new ContentDescriptor();
            if (isset($paramData['name']) && is_string($paramData['name'])) {
                $content->setName($paramData['name']);
            }
            if (isset($paramData['description']) && is_string($paramData['description'])) {
                $content->setDescription($paramData['description']);
            }
            if (isset($paramData['required']) && is_bool($paramData['required'])) {
                $content->setRequired($paramData['required']);
            }
            if (isset($paramData['schema'])) {
                $content->setSchema((object) $paramData['schema']);
            }
            $params[] = $content;
        }
        $method->setParams($params);
    }

    private function setMethodResult(Method $method, array $methodData): void
    {
        if (!isset($methodData['result'])) {
            return;
        }

        $result = new ContentDescriptor();
        if (isset($methodData['result']['name']) && is_string($methodData['result']['name'])) {
            $result->setName($methodData['result']['name']);
        }
        if (isset($methodData['result']['description']) && is_string($methodData['result']['description'])) {
            $result->setDescription($methodData['result']['description']);
        }
        if (isset($methodData['result']['schema'])) {
            $result->setSchema((object) $methodData['result']['schema']);
        }
        $method->setResult($result);
    }

    private function setMethodErrors(Method $method, array $methodData): void
    {
        if (!isset($methodData['errors']) || !is_array($methodData['errors'])) {
            return;
        }

        $errors = [];
        foreach ($methodData['errors'] as $errorData) {
            $error = new Error();
            if (isset($errorData['code']) && (is_int($errorData['code']) || is_string($errorData['code']))) {
                $error->setCode(empty($errorData['code']) ? null : (int) $errorData['code']);
            }
            if (isset($errorData['message']) && is_string($errorData['message'])) {
                $error->setMessage($errorData['message']);
            }
            $errors[] = $error;
        }
        $method->setErrors($errors);
    }

    private function setMethodExamples(Method $method, array $methodData): void
    {
        if (!isset($methodData['examples']) || !is_array($methodData['examples'])) {
            return;
        }

        $examples = [];
        foreach ($methodData['examples'] as $exampleData) {
            $example = $this->buildExamplePairing($exampleData);
            $examples[] = $example;
        }
        $method->setExamples($examples);
    }

    private function buildExamplePairing(array $exampleData): ExamplePairingObject
    {
        $example = new ExamplePairingObject();

        if (isset($exampleData['name']) && is_string($exampleData['name'])) {
            $name = $exampleData['name'];
            if (!str_starts_with($name, self::MOCK_EXAMPLE_PREFIX)) {
                $name = self::MOCK_EXAMPLE_PREFIX . $name;
            }
            $example->setName($name);
        }

        if (isset($exampleData['description']) && is_string($exampleData['description'])) {
            $example->setDescription($exampleData['description']);
        }

        $this->setExampleParams($example, $exampleData);
        $this->setExampleResult($example, $exampleData);

        return $example;
    }

    private function setExampleParams(ExamplePairingObject $example, array $exampleData): void
    {
        if (!isset($exampleData['params']) || !is_array($exampleData['params'])) {
            return;
        }

        $paramExamples = [];
        foreach ($exampleData['params'] as $paramExample) {
            $paramObj = new ExampleObject();
            if (isset($paramExample['name']) && is_string($paramExample['name'])) {
                $paramObj->setName($paramExample['name']);
            }
            if (isset($paramExample['value'])) {
                $paramObj->setValue($paramExample['value']);
            }
            $paramExamples[] = $paramObj;
        }
        $example->setParams($paramExamples);
    }

    private function setExampleResult(ExamplePairingObject $example, array $exampleData): void
    {
        if (!isset($exampleData['result'])) {
            return;
        }

        $resultExample = new ExampleObject();
        if (isset($exampleData['result']['name']) && is_string($exampleData['result']['name'])) {
            $resultExample->setName($exampleData['result']['name']);
        }
        if (isset($exampleData['result']['value'])) {
            $resultExample->setValue($exampleData['result']['value']);
        }
        $example->setResult($resultExample);
    }
}
