<?php

declare(strict_types=1);

namespace App\Domain\Portal\Dictionary\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Common\DTO\Titleable;
use App\Domain\Portal\Dictionary\DTO\EnumCaseResponse;
use App\Domain\Portal\Dictionary\DTO\EnumResponse;
use App\System\DomainSourceCodeFinder;
use BackedEnum;
use ReflectionEnum;
use ReflectionEnumBackedCase;

class GetEnumerationsController
{
    public function __construct(
        private DomainSourceCodeFinder $domainSourceCodeFinder
    ) {
    }

    /**
     * @return FindResponse<EnumResponse>
     */
    #[RpcMethod(
        'portal.dictionary.getEnumerations',
        'Получение всех доменных перечислений, используемых в апи',
    )]
    public function __invoke(): FindResponse
    {
        $enums = [];
        foreach ($this->domainSourceCodeFinder->getEnumReflections() as $refEnum) {
            $parts = explode('\\', str_replace('', '', $refEnum->name));
            $domainPath = mb_strtolower($parts[2]);
            if (count($parts) === 6) {
                $domainPath .= '.' . mb_strtolower($parts[3]);
            }

            $cases = [];
            /** @var ReflectionEnumBackedCase $case */
            foreach ($refEnum->getCases() as $case) {
                $cases[] = new EnumCaseResponse(
                    name: $case->getName(),
                    value: $case->getBackingValue(),
                    title: $this->getTitle($refEnum, $case->getBackingValue()),
                );
            }

            $enums[] = new EnumResponse(
                domain: $domainPath,
                name: $refEnum->getShortName(),
                cases: $cases
            );
        }
        return new FindResponse($enums);
    }

    private function getTitle(ReflectionEnum $refEnum, int|string $value): ?string
    {
        if (!$refEnum->implementsInterface(Titleable::class)) {
            return null;
        }
        /** @var BackedEnum&Titleable $enumName */
        $enumName = $refEnum->getName();
        return $enumName::from($value)->getTitle();
    }
}
