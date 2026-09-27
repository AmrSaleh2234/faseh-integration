<?php

namespace App\Services\FasahPay;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FasahPayClient
{
    public function __construct(
        private readonly FasahPayAuthService $auth,
    ) {}

    public function http(): PendingRequest
    {
        $cfg = $this->auth->credentials();

        return Http::baseUrl(rtrim($cfg['invoice_base_url'], '/'))
            ->timeout((int) config('fasahpay.timeout', 45))
            ->acceptJson()
            ->withHeaders([
                'X-Tabadul-Client-Id' => $cfg['client_id'],
                'X-Tabadul-Client-Secret' => $cfg['client_secret'],
                'Authorization' => 'Bearer '.$this->auth->token(),
            ]);
    }

    /**
     * Create invoice on Fasah Pay.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createInvoice(string $type, array $payload): array
    {
        $path = $this->pathForType($type);
        $response = $this->send(fn () => $this->http()->post($path, $payload));

        if ($response->failed()) {
            throw new RuntimeException(
                "Fasah Pay createInvoice({$type}) failed: ".$response->status().' '.$response->body()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateInvoice(string $type, array $payload): array
    {
        $path = $this->pathForType($type);
        $response = $this->send(fn () => $this->http()->put($path, $payload));

        if ($response->failed()) {
            throw new RuntimeException(
                "Fasah Pay updateInvoice({$type}) failed: ".$response->status().' '.$response->body()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function subscribeInvoiceNotifications(string $notificationUrl): array
    {
        $response = $this->send(fn () => $this->http()->post('/subscribenotifications', [
            'notificationURL' => $notificationUrl,
            'providerType' => config('fasahpay.provider_type', 'GEN'),
        ]));

        if ($response->failed()) {
            throw new RuntimeException(
                'Fasah Pay subscribe invoice notifications failed: '.$response->status().' '.$response->body()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function subscribeSettlementNotifications(string $settlementUrl): array
    {
        $response = $this->send(fn () => $this->http()->post('/subscribe-settlement', [
            'providerType' => config('fasahpay.provider_type', 'GEN'),
            'settlementUrl' => $settlementUrl,
        ]));

        if ($response->failed()) {
            throw new RuntimeException(
                'Fasah Pay subscribe settlement notifications failed: '.$response->status().' '.$response->body()
            );
        }

        return $response->json() ?? [];
    }

    private function pathForType(string $type): string
    {
        return match (strtolower($type)) {
            'general' => '',
            'billoflading', 'bill_of_lading', 'bl' => '/billoflading',
            'declaration' => '/declaration',
            'custombroker', 'customs_broker', 'broker' => '/custombroker',
            'importer' => '/importer',
            'shippingagent', 'shipping_agent' => '/shippingagent',
            default => throw new RuntimeException("Unsupported Fasah invoice type: {$type}"),
        };
    }

    /**
     * Retry once on 401 with a fresh token.
     *
     * @param  callable(): Response  $callback
     */
    private function send(callable $callback): Response
    {
        $response = $callback();

        if ($response->status() === 401) {
            $this->auth->forgetToken();
            $response = $callback();
        }

        return $response;
    }
}
