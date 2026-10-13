<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;

use function S2J\WebinarService\build_oauth_authorize_url;
use function S2J\WebinarService\build_oauth_refresh_request;
use function S2J\WebinarService\build_oauth_token_exchange_request;

final class OAuthMaterialsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function config(): array
    {
        return [
            'client_id' => 'cid',
            'client_secret' => 'top-secret',
            'redirect_uri' => 'https://example.test/cb',
        ];
    }

    public function testMissingConfigKeyThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        build_oauth_authorize_url(['client_id' => 'x', 'client_secret' => 'y']);
    }

    public function testTokenMaterialDoesNotEmbedClientSecret(): void
    {
        $material = build_oauth_token_exchange_request($this->config(), 'auth-code');
        self::assertArrayNotHasKey('client_secret', $material['body']);
        self::assertStringNotContainsString('top-secret', json_encode($material, JSON_THROW_ON_ERROR));
    }

    public function testRefreshMaterialDoesNotEmbedClientSecret(): void
    {
        $material = build_oauth_refresh_request($this->config(), 'refresh-token');
        self::assertArrayNotHasKey('client_secret', $material['body']);
    }

    public function testAuthorizeUrlDoesNotContainClientSecret(): void
    {
        $url = build_oauth_authorize_url($this->config());
        self::assertStringNotContainsString('top-secret', $url);
        self::assertStringContainsString('client_id=cid', $url);
    }

    public function testAuthorizeUrlIncludesAllOAuthScopesFromSpec(): void
    {
        $url = build_oauth_authorize_url($this->config());
        foreach (
            [
                'webinar%3Awrite%3Awebinar',
                'webinar%3Aread%3Awebinar',
                'webinar%3Aupdate%3Awebinar',
                'webinar%3Adelete%3Awebinar',
                'webinar%3Awrite%3Apanelist',
                'webinar%3Adelete%3Apanelist',
                'webinar%3Aupdate%3Asurvey',
                'user%3Aread%3Auser',
            ] as $scope
        ) {
            self::assertStringContainsString($scope, $url);
        }
    }

    public function testRefreshRequestBodyAndFullTokenUrl(): void
    {
        $material = build_oauth_refresh_request($this->config(), 'rtok');
        self::assertSame('POST', $material['method']);
        self::assertSame('https://zoom.us/oauth/token', $material['path']);
        self::assertSame('refresh_token', $material['body']['grant_type']);
        self::assertSame('rtok', $material['body']['refresh_token']);
    }

    public function testTokenExchangeMissingRedirectUriThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        build_oauth_token_exchange_request(
            ['client_id' => 'a', 'client_secret' => 'b'],
            'code'
        );
    }

    public function testRefreshMissingClientSecretThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        build_oauth_refresh_request(['client_id' => 'a', 'redirect_uri' => 'https://x.test/cb'], 'rt');
    }
}
