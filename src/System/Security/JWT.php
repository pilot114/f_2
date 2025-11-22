<?php

declare(strict_types=1);

namespace App\System\Security;

/**
 * TODO: lcobucci/jwt
 */
class JWT
{
    public function __construct(
        private string $secret,
    ) {
    }

    public function decode(string $token, ?string $key = null): false|array
    {
        $tokenData = explode('.', $token);
        if (count($tokenData) < 3) {
            return false;
        }

        [$rawHeader, $rawPayload, $rawSign] = $tokenData;
        if ($rawHeader === '' || $rawHeader === '0') {
            return false;
        }
        $header = base64_decode($rawHeader, true);
        if (!$header) {
            return false;
        }

        $header = json_decode($header, true);
        if (!$header) {
            return false;
        }
        /** @var array $header */

        if (empty($header['typ'])) {
            return false;
        }
        if ($header['typ'] !== 'JWT') {
            return false;
        }
        if (empty($header['alg'])) {
            return false;
        }
        if (!$this->checkSupportedAlgo($header['alg'])) {
            return false;
        }

        if ($rawSign !== hash_hmac($header['alg'], $rawHeader . '.' . $rawPayload, base64_encode($this->secret))) {
            return false;
        }

        $payload = base64_decode($rawPayload, true);
        if (!$payload) {
            return false;
        }

        if ($key) {
            $key = openssl_pkey_get_private($key);
            if (!$key) {
                return false;
            }
            openssl_private_decrypt($payload, $payload, $key, OPENSSL_PKCS1_OAEP_PADDING);
        }

        return (array) json_decode($payload, true);
    }

    private function checkSupportedAlgo(string $name): bool
    {
        return in_array($name, hash_algos(), true);
    }
}
