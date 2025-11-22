<?php

declare(strict_types=1);

namespace App\System\Security\Attribute;

use App\Common\Attribute\AbstractAccessRightAttribute;
use App\System\ControllerAttributeLoader;
use Generator;
use ReflectionMethod;

/**
 * @extends ControllerAttributeLoader<AbstractAccessRightAttribute>
 */
abstract class AbstractAccessRightAttributeLoader extends ControllerAttributeLoader
{
    protected function prepareAttribute(mixed $attribute, ReflectionMethod $refMethod): mixed
    {
        return $attribute;
    }

    /**
     * @return Generator<string, AbstractAccessRightAttribute>
     */
    public function load(): Generator
    {
        foreach ($this->doLoad($this->getAttributeClass()) as $fqn => $attribute) {
            /** @var AbstractAccessRightAttribute $attribute */
            yield $fqn => $attribute;
        }
    }

    public function loadByFqn(string $search): ?AbstractAccessRightAttribute
    {
        foreach ($this->load() as $fqn => $attribute) {
            if ($fqn === $search) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * @return class-string<AbstractAccessRightAttribute>
     */
    abstract protected function getAttributeClass(): string;
}
