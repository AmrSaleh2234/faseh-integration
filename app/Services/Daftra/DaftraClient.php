<?php

namespace App\Services\Daftra;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DaftraClient
{
    public function http(): PendingRequest
    {
        $base = config('daftra.base_url');
        $key = config('daftra.api_key');

        if (! $base || ! $key) {
            throw new RuntimeException('Daftra is not configured. Set DAFTRA_BASE_URL and DAFTRA_API_KEY in .env');
        }

        return Http::baseUrl($base)
            ->timeout((int) config('daftra.timeout', 30))
            ->acceptJson()
            ->withHeaders([
                'apikey' => $key,
                'Authorization' => 'Bearer '.$key,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getInvoice(int|string $invoiceId): array
    {
        $response = $this->http()->get("/invoices/{$invoiceId}.json");

        if ($response->failed()) {
            throw new RuntimeException(
                "Daftra getInvoice({$invoiceId}) failed: ".$response->status().' '.$response->body()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function listInvoices(int $page = 1, int $limit = 20, array $query = []): array
    {
        $response = $this->http()->get('/invoices.json', array_merge([
            'page' => $page,
            'limit' => $limit,
        ], $query));

        if ($response->failed()) {
            throw new RuntimeException(
                'Daftra listInvoices failed: '.$response->status().' '.$response->body()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Mark invoice paid in Daftra after Fasah SADAD payment.
     *
     * @return array<string, mixed>
     */
    public function addInvoicePayment(
        int|string $invoiceId,
        float $amount,
        string $transactionId,
        ?string $date = null,
        ?string $paymentMethod = null,
    ): array {
        $payload = [
            'InvoicePayment' => [
                'invoice_id' => (int) $invoiceId,
                'amount' => $amount,
                'payment_method' => $paymentMethod ?: config('daftra.payment_method', 'bank'),
                'date' => $date ?: now()->toDateString(),
                'transaction_id' => $transactionId,
            ],
        ];

        $response = $this->http()->post('/invoice_payments.json', $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                "Daftra addInvoicePayment({$invoiceId}) failed: ".$response->status().' '.$response->body()
            );
        }

        return $response->json() ?? [];
    }
}
