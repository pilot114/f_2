<?php

declare(strict_types=1);

namespace App\System\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;

class AccessTokenExtractor implements AccessTokenExtractorInterface
{
    public function extractAccessToken(Request $request): ?string
    {
        $token = $request->headers->get('Authorization') ?: $request->cookies->get('inner_token');
        if ($token === null) {
            return null;
        }
        return str_replace('Bearer ', '', (string) $token);
    }
}
