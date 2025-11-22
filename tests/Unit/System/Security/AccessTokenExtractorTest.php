<?php

declare(strict_types=1);

use App\System\Security\AccessTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;

beforeEach(function (): void {
    $this->extractor = new AccessTokenExtractor();
});

it('implements access token extractor interface', function (): void {
    expect($this->extractor)->toBeInstanceOf(AccessTokenExtractorInterface::class);
});

it('extracts token from authorization header with bearer prefix', function (): void {
    $request = new Request();
    $request->headers->set('Authorization', 'Bearer test-token-123');

    $token = $this->extractor->extractAccessToken($request);

    expect($token)->toBe('test-token-123');
});

it('extracts token from authorization header without bearer prefix', function (): void {
    $request = new Request();
    $request->headers->set('Authorization', 'test-token-456');

    $token = $this->extractor->extractAccessToken($request);

    expect($token)->toBe('test-token-456');
});

it('extracts token from inner_token cookie when authorization header is not present', function (): void {
    $request = new Request();
    $request->cookies->set('inner_token', 'cookie-token-789');

    $token = $this->extractor->extractAccessToken($request);

    expect($token)->toBe('cookie-token-789');
});

it('prefers authorization header over cookie', function (): void {
    $request = new Request();
    $request->headers->set('Authorization', 'Bearer header-token');
    $request->cookies->set('inner_token', 'cookie-token');

    $token = $this->extractor->extractAccessToken($request);

    expect($token)->toBe('header-token');
});

it('returns null when no token is found', function (): void {
    $request = new Request();

    $token = $this->extractor->extractAccessToken($request);

    expect($token)->toBeNull();
});

it('handles empty authorization header', function (): void {
    $request = new Request();
    $request->headers->set('Authorization', '');

    $token = $this->extractor->extractAccessToken($request);

    expect($token)->toBeNull();
});

it('handles bearer prefix with multiple spaces', function (): void {
    $request = new Request();
    $request->headers->set('Authorization', 'Bearer  token-with-spaces');

    $token = $this->extractor->extractAccessToken($request);

    expect($token)->toBe(' token-with-spaces');
});
