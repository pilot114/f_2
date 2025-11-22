<?php

declare(strict_types=1);

namespace App\System;

use Generator;
use ReflectionMethod;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @template T
 */
abstract class ControllerAttributeLoader
{
    public function __construct(
        private DomainSourceCodeFinder $fileLoader,
        private CacheInterface         $cache,
        private string                 $env,
    ) {
    }

    /**
     * @return Generator<string, T>
     */
    abstract public function load(): Generator;

    abstract protected function prepareAttribute(mixed $attribute, ReflectionMethod $refMethod): mixed;

    /**
     * @param class-string<T> $attributeName
     */
    protected function doLoad(string $attributeName): array
    {
        if ($this->env !== 'prod') {
            return $this->getAttributes($attributeName);
        }

        return $this->cache->get(
            'ControllerAttributeLoader_doLoad_' . last(explode('\\', $attributeName)),
            fn (): array => $this->getAttributes($attributeName)
        );
    }

    /**
     * @param class-string $attributeName
     */
    private function getAttributes(string $attributeName): array
    {
        $tmp = [];
        foreach ($this->fileLoader->getClassReflections('*Controller.php') as $refController) {
            foreach ($refController->getMethods() as $refMethod) {
                $attribute = $refMethod->getAttributes($attributeName)[0] ?? null;
                $attribute = $attribute?->newInstance();
                if ($attribute === null) {
                    continue;
                }
                $fqn = "$refController->name::$refMethod->name";
                $tmp[$fqn] = $this->prepareAttribute($attribute, $refMethod);
            }
        }
        return $tmp;
    }
}
