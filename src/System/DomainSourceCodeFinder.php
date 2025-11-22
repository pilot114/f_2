<?php

declare(strict_types=1);

namespace App\System;

use BackedEnum;
use Generator;
use ReflectionClass;
use ReflectionEnum;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DomainSourceCodeFinder
{
    public function __construct(
        private Finder $finder,
        private string $projectDir,
    ) {
    }

    public function getDomainDirs(): array
    {
        $dirs = [];
        $finder = clone $this->finder;
        $finder->files()->in($this->projectDir . '/src/Domain')->directories();
        foreach ($finder as $dir) {
            $path = $dir->getRelativePathname();
            $parts = explode('/', $path);
            if (count($parts) === 2) {
                $dirs[] = $parts;
            }
        }
        return $dirs;
    }

    /**
     * @return Generator<ReflectionEnum<BackedEnum>>
     */
    public function getEnumReflections(): Generator
    {
        $finder = clone $this->finder;
        $finder->files()->in($this->projectDir . '/src/Domain')->name('*.php');
        foreach ($finder as $file) {
            $ref = $this->fileToReflection($file);
            if ($ref instanceof ReflectionEnum) {
                yield $ref;
            }
        }
    }

    /**
     * @return Generator<ReflectionClass<object>>
     */
    public function getClassReflections(string $name, string $path = ''): Generator
    {
        $finder = clone $this->finder;
        $finder->files()->in($this->projectDir . '/src/Domain')->name($name);
        if ($path !== '' && $path !== '0') {
            $finder->path($path);
        }
        foreach ($finder as $file) {
            $ref = $this->fileToReflection($file);
            if (!$ref instanceof ReflectionEnum) {
                yield $ref;
            }
        }
    }

    /**
     * @return ReflectionClass<object> | ReflectionEnum
     */
    private function fileToReflection(SplFileInfo $file): ReflectionClass | ReflectionEnum
    {
        $path = $file->getRelativePathname();
        $path = str_replace('.php', '', $path);
        /** @var class-string $className */
        $className = 'App\\Domain\\' . str_replace('/', '\\', $path);
        if (enum_exists($className)) {
            return new ReflectionEnum($className);
        }
        return new ReflectionClass($className);
    }
}
