<?php

namespace App\Actions;

use App\Models\FasahCallbackLog;
use App\Models\Invoice;
use App\Models\LocalFasahSync;
use App\Services\InvoiceSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class HandleFasahCallback
{
    public function __construct(private readonly InvoiceSyncService $daftraSync) {}

    public function invoice(array $payload): bool
    {
        $log = FasahCallbackLog::create([
            'type' => 'invoice',
            'fasah_invoice_number' => $payload['fasahpayInvoiceNumber'] ?? $payload['FasahPayInvoiceNumber'] ?? null,
            'internal_invoice_number' => $payload['internalInvoiceNumber'] ?? null,
            'sadad_number' => $payload['sadadNumber'] ?? null,
            'invoice_status' => $payload['invoiceStatus'] ?? null,
            'payload' => $payload,
        ]);
        try {
            $local = $this->findLocal($payload);
            if ($local) {
                $this->requireLocalCallbackSecret();
                $this->handleLocalInvoice($local, $payload);
            } else {
                $this->daftraSync->handleInvoiceNotification($payload);
            }
            $log->update(['processed' => true]);
            return true;
        } catch (Throwable $e) {
            $this->fail($log, $e);
            return false;
        }
    }

    public function settlements(array $body): bool
    {
        $allProcessed = true;
        foreach (array_is_list($body) ? $body : [$body] as $payload) {
            if (! is_array($payload)) continue;
            $log = FasahCallbackLog::create([
                'type' => 'settlement',
                'fasah_invoice_number' => $payload['invoiceNumber'] ?? $payload['fasahpayInvoiceNumber'] ?? null,
                'internal_invoice_number' => $payload['internalInvoiceNumber'] ?? null,
                'sadad_number' => $payload['sadadNumber'] ?? null,
                'invoice_status' => 'SETTLED',
                'payload' => $payload,
            ]);
            try {
                $local = $this->findLocal($payload);
                if ($local) {
                    $this->requireLocalCallbackSecret();
                    DB::transaction(function () use ($local, $payload) {
                        $sync = LocalFasahSync::whereKey($local->id)->lockForUpdate()->firstOrFail();
                        $sync->update(['fasah_status' => 'SETTLED', 'sadad_number' => $payload['sadadNumber'] ?? $sync->sadad_number, 'settled_at' => now()]);
                    });
                } else {
                    $this->daftraSync->handleSettlementNotification($payload);
                }
                $log->update(['processed' => true]);
            } catch (Throwable $e) {
                $this->fail($log, $e);
                $allProcessed = false;
            }
        }
        return $allProcessed;
    }

    private function findLocal(array $payload): ?LocalFasahSync
    {
        $number = (string) ($payload['fasahpayInvoiceNumber'] ?? $payload['FasahPayInvoiceNumber'] ?? $payload['invoiceNumber'] ?? '');
        $internal = (string) ($payload['internalInvoiceNumber'] ?? '');
        if ($number !== '') {
            return LocalFasahSync::where('fasah_invoice_number', $number)->first();
        }
        return $internal !== '' ? LocalFasahSync::where('internal_invoice_number', $internal)->first() : null;
    }

    private function handleLocalInvoice(LocalFasahSync $local, array $payload): void
    {
        DB::transaction(function () use ($local, $payload) {
            $sync = LocalFasahSync::whereKey($local->id)->lockForUpdate()->firstOrFail();
            $status = strtoupper((string) ($payload['invoiceStatus'] ?? ''));
            $sync->fasah_status = $status;
            $sync->sadad_number = $payload['sadadNumber'] ?? $sync->sadad_number;
            if ($status === 'PAID') {
                $invoice = Invoice::whereKey($sync->invoice_id)->lockForUpdate()->firstOrFail();
                $key = 'fasah:'.$sync->internal_invoice_number.':paid';
                if ($invoice->balance > 0 && ! $invoice->payments()->where('external_key', $key)->exists()) {
                    $amount = $invoice->balance;
                    $invoice->payments()->create([
                        'recorded_by' => null,
                        'amount' => number_format($amount, 2, '.', ''),
                        'paid_on' => now()->toDateString(),
                        'method' => 'Fasah Pay',
                        'reference' => $sync->sadad_number,
                        'external_key' => $key,
                    ]);
                    $invoice->amount_paid = number_format((float) $invoice->amount_paid + $amount, 2, '.', '');
                    $invoice->save();
                }
                $sync->paid_at ??= now();
            }
            $sync->save();
        });
    }

    private function requireLocalCallbackSecret(): void
    {
        if (! config('fasahpay.callback_secret')) {
            throw new RuntimeException('Local Fasah callbacks require FASAHPAY_CALLBACK_SECRET.');
        }
    }

    private function fail(FasahCallbackLog $log, Throwable $e): void
    {
        Log::error('Fasah callback processing failed', ['error' => $e->getMessage(), 'callback_log_id' => $log->id]);
        $log->update(['processed' => false, 'processing_error' => mb_substr($e->getMessage(), 0, 500)]);
    }
}
