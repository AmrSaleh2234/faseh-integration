<?php

namespace App\Services\FasahPay;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FasahPayAuthService
{
    public function token(): string
    {
        $env = $this->environment();
        $cacheKey = "fasahpay.jwt.{$env}";

        return Cache::remember($cacheKey, (int) config('fasahpay.token_cache_ttl', 3000), function () {
            return $this->requestToken();
        });
    }

    public function forgetToken(): void
    {
        Cache::forget('fasahpay.jwt.'.$this->environment());
    }

    public function requestToken(): string
    {
        $cfg = $this->credentials();

        $response = Http::timeout((int) config('fasahpay.timeout', 45))
            ->acceptJson()
            ->withHeaders([
                'X-Tabadul-Client-Id' => $cfg['client_id'],
                'X-Tabadul-Client-Secret' => $cfg['client_secret'],
            ])
            ->post($cfg['token_url'], [
                'username' => $cfg['username'],
                'password' => $cfg['password'],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Fasah Pay token request failed: '.$response->status().' '.$response->body()
            );
        }

        $token = $response->json('token') ?? $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Fasah Pay token missing in response: '.$response->body());
        }

        // Response may already include "Bearer ..."
        return str_starts_with($token, 'Bearer ') ? substr($token, 7) : $token;
    }

    /**
     * @return array{token_url:string,invoice_base_url:string,client_id:string,client_secret:string,username:string,password:string}
     */
    public function credentials(): array
    {
        $env = $this->environment();
        $cfg = config("fasahpay.{$env}");

        foreach (['token_url', 'invoice_base_url', 'client_id', 'client_secret', 'username', 'password'] as $key) {
            if (empty($cfg[$key])) {
                throw new RuntimeException("Missing Fasah Pay config fasahpay.{$env}.{$key} — check .env");
            }
        }

        return $cfg;
    }

    public function environment(): string
    {
        $env = config('fasahpay.env', 'sandbox');

        return in_array($env, ['sandbox', 'production'], true) ? $env : 'sandbox';
    }
}
