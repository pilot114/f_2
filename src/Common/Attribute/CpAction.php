<?php

declare(strict_types=1);

namespace App\Common\Attribute;

use Attribute;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

#[Attribute(Attribute::TARGET_METHOD)]
class CpAction extends AbstractAccessRightAttribute
{
    public function check(): bool
    {
        preg_match_all('/[\w\.]+/', $this->expression, $matches);
        $vars = [];
        foreach ($matches[0] as $name) {
            $preparedName = str_replace('.', '__', $name);
            $vars[$preparedName] = $this->hasPermission($name);
        }
        $expressionLanguage = new ExpressionLanguage();
        return (bool) $expressionLanguage->evaluate(str_replace('.', '__', $this->expression), $vars);
    }

    protected function hasPermission(string $name): bool
    {
        return $this->secRepo->hasCpAction($this->currentUser->id, $name);
    }
}
