<?php

declare(strict_types=1);

use App\System\Security\JWT;

beforeEach(function (): void {
    $this->secret = 'test-secret-key';
    $this->jwt = new JWT($this->secret);
});

it('decodes valid jwt token successfully', function (): void {
    $payload = [
        'user_id' => 123,
        'email'   => 'test@example.com',
    ];
    $header = [
        'typ' => 'JWT',
        'alg' => 'sha256',
    ];

    $headerEncoded = base64_encode(json_encode($header));
    $payloadEncoded = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, base64_encode($this->secret));

    $token = $headerEncoded . '.' . $payloadEncoded . '.' . $signature;

    $result = $this->jwt->decode($token);

    expect($result)->toBe($payload);
});

it('returns false for token with insufficient parts', function (): void {
    $result = $this->jwt->decode('invalid.token');

    expect($result)->toBe(false);
});

it('returns false for token with empty header', function (): void {
    $result = $this->jwt->decode('..signature');

    expect($result)->toBe(false);
});

it('returns false for token with invalid base64 header', function (): void {
    $result = $this->jwt->decode('invalid-base64.payload.signature');

    expect($result)->toBe(false);
});

it('returns false for token with invalid json header', function (): void {
    $invalidHeader = base64_encode('{invalid json');
    $result = $this->jwt->decode($invalidHeader . '.payload.signature');

    expect($result)->toBe(false);
});

it('returns false for token without typ field in header', function (): void {
    $header = [
        'alg' => 'sha256',
    ];
    $headerEncoded = base64_encode(json_encode($header));

    $result = $this->jwt->decode($headerEncoded . '.payload.signature');

    expect($result)->toBe(false);
});

it('returns false for token with incorrect typ field', function (): void {
    $header = [
        'typ' => 'INVALID',
        'alg' => 'sha256',
    ];
    $headerEncoded = base64_encode(json_encode($header));

    $result = $this->jwt->decode($headerEncoded . '.payload.signature');

    expect($result)->toBe(false);
});

it('returns false for token without alg field in header', function (): void {
    $header = [
        'typ' => 'JWT',
    ];
    $headerEncoded = base64_encode(json_encode($header));

    $result = $this->jwt->decode($headerEncoded . '.payload.signature');

    expect($result)->toBe(false);
});

it('returns false for token with unsupported algorithm', function (): void {
    $header = [
        'typ' => 'JWT',
        'alg' => 'unsupported-algorithm',
    ];
    $headerEncoded = base64_encode(json_encode($header));

    $result = $this->jwt->decode($headerEncoded . '.payload.signature');

    expect($result)->toBe(false);
});

it('returns false for token with invalid signature', function (): void {
    $payload = [
        'user_id' => 123,
    ];
    $header = [
        'typ' => 'JWT',
        'alg' => 'sha256',
    ];

    $headerEncoded = base64_encode(json_encode($header));
    $payloadEncoded = base64_encode(json_encode($payload));
    $invalidSignature = 'invalid-signature';

    $token = $headerEncoded . '.' . $payloadEncoded . '.' . $invalidSignature;

    $result = $this->jwt->decode($token);

    expect($result)->toBe(false);
});

it('returns false for token with invalid base64 payload', function (): void {
    $header = [
        'typ' => 'JWT',
        'alg' => 'sha256',
    ];
    $headerEncoded = base64_encode(json_encode($header));
    $signature = hash_hmac('sha256', $headerEncoded . '.invalid-payload', base64_encode($this->secret));

    $token = $headerEncoded . '.invalid-payload.' . $signature;

    $result = $this->jwt->decode($token);

    expect($result)->toBe(false);
});

it('supports different hash algorithms', function (): void {
    $payload = [
        'test' => 'data',
    ];
    $header = [
        'typ' => 'JWT',
        'alg' => 'md5',
    ];

    $headerEncoded = base64_encode(json_encode($header));
    $payloadEncoded = base64_encode(json_encode($payload));
    $signature = hash_hmac('md5', $headerEncoded . '.' . $payloadEncoded, base64_encode($this->secret));

    $token = $headerEncoded . '.' . $payloadEncoded . '.' . $signature;

    $result = $this->jwt->decode($token);

    expect($result)->toBe($payload);
});
