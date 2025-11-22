<?php

declare(strict_types=1);

namespace App\System\RPC\DTO;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Object_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ReflectionClass;

class DocBlockParser
{
    private DocBlock $docBlock;

    public function __construct(
        string $docString,
    ) {
        $factory = DocBlockFactory::createInstance();
        $this->docBlock = $factory->create($docString);
    }

    /**
     * @param class-string $className
     * @return ?class-string
     */
    public function getArrayType(string $className, string $parameterName): ?string
    {
        foreach ($this->docBlock->getTagsByName('param') as $tag) {
            if (!$tag instanceof Param) {
                continue;
            }
            if ($tag->getVariableName() !== $parameterName) {
                continue;
            }
            $type = $tag->getType();
            if (!$type instanceof Array_) {
                continue;
            }
            $objectType = $type->getValueType();
            if (!$objectType instanceof Object_) {
                continue;
            }
            if (!$objectType->getFqsen() instanceof Fqsen) {
                continue;
            }
            $type = $objectType->getFqsen()->getName();

            $typeResolver = new TypeResolver();
            $context = $this->getContext($className);
            if (!$context instanceof Context) {
                continue;
            }
            $resolvedType = $typeResolver->resolve($type, $context);
            if (!$resolvedType instanceof Object_) {
                continue;
            }
            $fqsen = $resolvedType->getFqsen();
            if ($fqsen instanceof Fqsen) {
                $fqsen = (string) $fqsen;
                /** @var class-string $fqsen */
                return $fqsen;
            }
        }
        return null;
    }

    /**
     * @param class-string $className
     */
    private function getContext(string $className): ?Context
    {
        $reflector = new ReflectionClass($className);
        $fileName = $reflector->getFileName();
        if ($fileName === false) {
            return null;
        }
        $code = file_get_contents($fileName);
        if ($code === false) {
            return null;
        }

        $parser = (new ParserFactory())->createForHostVersion();
        $ast = $parser->parse($code);
        if ($ast === null) {
            return null;
        }

        $visitor = new class() extends NodeVisitorAbstract {
            public string $namespace = '';
            public array $uses = [];

            public function enterNode(Node $node): null
            {
                if ($node instanceof Namespace_) {
                    if ($node->name === null) {
                        return null;
                    }
                    $this->namespace = $node->name->toString();
                }

                if ($node instanceof Use_) {
                    foreach ($node->uses as $use) {
                        $alias = $use->alias ? $use->alias->toString() : $use->name->getLast();
                        $this->uses[$alias] = $use->name->toString();
                    }
                }
                return null;
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        $namespace = $visitor->namespace;
        $uses = $visitor->uses;
        return new Context($namespace, $uses);
    }
}
