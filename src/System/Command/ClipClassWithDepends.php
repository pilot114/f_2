<?php

declare(strict_types=1);

namespace App\System\Command;

use App\Common\Service\File\TempFileRegistry;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'system:clipClassWithDepends', description: 'скопировать в буфер обмена класс и его зависимости')]
class ClipClassWithDepends extends Command
{
    public function __construct(
        private readonly TempFileRegistry $tempFileRegistry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('className', InputArgument::REQUIRED, 'имя класса')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var class-string $className
         */
        $className = $input->getArgument('className');

        $classCode = $this->getSourceByClassName($className);

        $result = "### $className ###\n";
        $result .= "$classCode\n";

        foreach ($this->getUses($classCode) as $useClassName) {
            $result .= "### $useClassName ###\n";
            $code = $this->getSourceByClassName($useClassName);
            $result .= "$code\n";
        }
        $length = strlen($result);

        $file = $this->tempFileRegistry->createFile($result);

        `cat $file | xclip -selection clipboard > /dev/null`;

        $output->writeln("Добавлено в буфер обмена ($length)");

        return Command::SUCCESS;
    }

    /**
     * @param class-string $className
     */
    protected function getSourceByClassName(string $className): string
    {
        $reflectionClass = new ReflectionClass($className);
        $filename = $reflectionClass->getFileName();
        return (string) file_get_contents((string) $filename);
    }

    protected function getUses(string $source): array
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $ast = $parser->parse($source);

        if ($ast === null) {
            return [];
        }

        $visitor = new class() extends NodeVisitorAbstract {
            private array $useStatements = [];

            public function enterNode(Node $node): null
            {
                if ($node instanceof Use_) {
                    foreach ($node->uses as $use) {
                        $name = $use->name->toString();
                        /**
                         * @var class-string $name
                         */
                        $class = new ReflectionClass($name);
                        if ($class->isUserDefined() && !$class->isInterface()) {
                            $this->useStatements[] = $name;
                        }
                    }
                }
                return null;
            }

            public function getUseStatements(): array
            {
                return $this->useStatements;
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);
        return $visitor->getUseStatements();
    }
}
