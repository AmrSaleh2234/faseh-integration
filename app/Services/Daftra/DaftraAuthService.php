<?php

namespace App\Services\Daftra;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DaftraAuthService
{
    public function token(): string
    {
        return Cache::remember(
            'daftra.oauth.token',
            (int) config('daftra.token_cache_ttl', 86000),
            fn () => $this->requestToken()
        );
    }

    public function forgetToken(): void
    {
        Cache::forget('daftra.oauth.token');
    }

    public function requestToken(): string
    {
        $clientId = config('daftra.client_id');
        $clientSecret = config('daftra.client_secret');
        $username = config('daftra.username');
        $password = config('daftra.password');
        $url = $this->tokenUrl();

        foreach ([
            'DAFTRA_CLIENT_ID' => $clientId,
            'DAFTRA_CLIENT_SECRET' => $clientSecret,
            'DAFTRA_USERNAME' => $username,
            'DAFTRA_PASSWORD' => $password,
        ] as $envKey => $value) {
            if (empty($value)) {
                throw new RuntimeException("Daftra OAuth is not configured. Set {$envKey} in .env");
            }
        }

        $response = Http::asMultipart()
            ->acceptJson()
            ->post($url, [
                ['name' => 'grant_type', 'contents' => 'password'],
                ['name' => 'client_id', 'contents' => $clientId],
                ['name' => 'client_secret', 'contents' => $clientSecret],
                ['name' => 'username', 'contents' => $username],
                ['name' => 'password', 'contents' => $password],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Daftra OAuth token request failed: '.$response->status().' '.$response->body()
            );
        }

        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Daftra OAuth access_token missing in response: '.$response->body());
        }

        return $token;
    }

    private function tokenUrl(): string
    {
        $override = config('daftra.oauth_token_url');

        if (! empty($override)) {
            return $override;
        }

        $base = (string) config('daftra.base_url');
        $parts = parse_url($base);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';

        if ($host === '') {
            throw new RuntimeException('Cannot derive Daftra OAuth token URL: DAFTRA_BASE_URL is not set.');
        }

        return "{$scheme}://{$host}/v2/oauth/token";
    }
}
