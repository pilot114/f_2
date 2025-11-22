<?php

declare(strict_types=1);

namespace App\System;

use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\Yaml\Yaml;

class RefreshGenericsService
{
    protected array $repoServices = [];
    protected array $useCaseServices = [];

    public function __construct(
        private PhpStanExtractor       $phpStanExtractor,
        private DomainSourceCodeFinder $fileLoader,
        private Filesystem             $filesystem,
        private string                 $projectDir,
    ) {
    }

    public function writeFile(): void
    {
        foreach ($this->fileLoader->getClassReflections('*UseCase.php') as $useCase) {
            $constructor = $useCase->getConstructor();
            if ($constructor === null) {
                continue;
            }
            foreach ($constructor->getParameters() as $parameter) {
                $this->extractServicesFromGeneric($useCase->name, $parameter->name);
            }
        }

        // в командах можно внедрить репозиторий без useCase
        foreach ($this->fileLoader->getClassReflections('*Command.php') as $useCase) {
            $constructor = $useCase->getConstructor();
            if ($constructor === null) {
                continue;
            }
            foreach ($constructor->getParameters() as $parameter) {
                $this->extractServicesFromGeneric($useCase->name, $parameter->name);
            }
        }

        $services = [
            '_defaults' => [
                'autowire'      => true,
                'autoconfigure' => true,
            ],
            'App\\' => [
                'resource' => '%kernel.project_dir%/src/',
                'exclude'  => [
                    '%kernel.project_dir%/src/Kernel.php',
                ],
            ],
            ...$this->repoServices,
            ...$this->useCaseServices,
        ];

        $content = Yaml::dump([
            'services' => $services,
        ], 10);
        $this->filesystem->dumpFile($this->projectDir . '/config/generics.yaml', $content);
    }

    protected function extractServicesFromGeneric(string $useCaseName, string $parameterName): void
    {
        $generic = $this->phpStanExtractor->getType($useCaseName, $parameterName);
        if (!$generic instanceof GenericType) {
            return;
        }
        /** @var ObjectType<object> $genericObject */
        $genericObject = $generic->getWrappedType();

        $baseType = $genericObject->getClassName();
        $isCommand = str_contains($baseType, 'CommandRepository');
        $isQuery = str_contains($baseType, 'QueryRepository');
        if (!$isCommand && !$isQuery) {
            return;
        }

        /** @var ObjectType<object> $mainVariableType */
        $mainVariableType = $generic->getVariableTypes()[0];
        $mainVariableType = $mainVariableType->getClassName();

        $factoryMethod = $isCommand ? 'command' : 'query';
        $prefix = ucfirst($factoryMethod);

        /** @var ObjectType<object> $mainVariableType */
        $shortType = $prefix . 'Repository' . (new ReflectionClass($mainVariableType))->getShortName();

        $this->repoServices[$shortType] = [
            'class'     => 'Database\\ORM\\' . $prefix . 'Repository',
            'factory'   => ['@App\System\Factory\RepositoryFactory', $factoryMethod],
            'arguments' => [
                '$entityName' => $mainVariableType,
            ],
        ];
        $this->useCaseServices[$useCaseName]['arguments']["$$parameterName"] = "@$shortType";
    }
}
