<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withParallel()
    ->withPreparedSets(
        psr12: true,
        arrays: true,
        comments: true,
        docblocks: true,
        spaces: true,
        namespaces: true,
        strict: true,
    )

    ->withRules([
        NoUnusedImportsFixer::class,
    ])
    ->withConfiguredRule(
        BinaryOperatorSpacesFixer::class,
        ['operators' => ['=>' => 'align_single_space_minimal']]
    )
    ->withSkip([
        MethodArgumentSpaceFixer::class,
        TypeDeclarationSpacesFixer::class,
        PhpdocLineSpanFixer::class,
        UnaryOperatorSpacesFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
        ClassAttributesSeparationFixer::class,
    ])
     ;
