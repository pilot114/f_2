<?php

declare(strict_types=1);

namespace App\Common\Attribute;

use Attribute;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Attribute(Attribute::TARGET_METHOD)]
class RpcMethod
{
    /** @var array<string, RpcParam> */
    public array   $params = [];
    public array   $resultSchema = [];
    public ?string $resultSchemaName = null;

    public array   $genericTypeSchema = [];
    public ?string $genericTypeSchemaName = null;

    public string $fqn;

    public function __construct(
        public string  $name,
        public string  $summary,
        public ?string $description = null,
        public array   $errors = [],
        public array   $examples = [],
        public array   $tags = [],
        public bool    $isDeprecated = false,
        // rpc параметры автоматически маппятся в единственный параметр (DTO)
        public bool    $isAutomapped = false,
    ) {
        $parts = explode('.', $name);
        if (count($parts) !== 3) {
            throw new BadRequestHttpException('RPC метод должен содержать имя домена, поддомена и юзкейса');
        }
        [$domain, $subDomain] = $parts;

        if (!in_array($domain, $this->tags, true)) {
            $this->tags[] = $domain;
        }
        if ($subDomain && !in_array($subDomain, $this->tags, true)) {
            $this->tags[] = $subDomain;
        }
    }

    public function isQuery(): bool
    {
        return str_contains($this->name, '.get')
            || str_contains($this->name, '.find')
            || str_contains($this->name, '.search')
            || str_contains($this->name, '.check')
        ;
    }
}
