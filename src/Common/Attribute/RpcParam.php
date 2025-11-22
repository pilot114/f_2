<?php

declare(strict_types=1);

namespace App\Common\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class RpcParam
{
    public ?array $schema = null;
    /**
     * @var class-string
     */
    public ?string $schemaName = null;

    public function __construct(
        public ?string $summary = null,
        public ?string $description = null,
        public bool $required = true,
        public bool $deprecated = false,
    ) {
    }
}
