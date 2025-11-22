<?php

declare(strict_types=1);

namespace App\System\Generate\Scenario;

use App\System\Generate\ScenarioInterface;
use Generator;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

class GenerateCommandRepository implements ScenarioInterface
{
    public function __construct(
        private string $repoName
    ) {
    }

    public function load(): void
    {
    }

    public function run(string $outputNamespace): Generator
    {
        $class = new ClassType($this->repoName);
        $class->setExtends('Database\ORM\CommandRepository');

        $file = new PhpFile();
        $file
            ->setStrictTypes()
            ->addNamespace($outputNamespace)
            ->addUse('Database\ORM\CommandRepository')
            ->add($class)
        ;

        yield $this->repoName . '.php' => (string) $file;
    }
}
