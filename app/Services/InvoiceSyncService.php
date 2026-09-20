<?php

namespace App\Services;

use App\Models\SyncedInvoice;
use App\Services\Daftra\DaftraClient;
use App\Services\FasahPay\FasahPayClient;
use App\Services\FasahPay\InvoicePayloadBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class InvoiceSyncService
{
    public function __construct(
        private readonly DaftraClient $daftra,
        private readonly FasahPayClient $fasah,
        private readonly InvoicePayloadBuilder $builder,
    ) {}

    /**
     * Pull a Daftra invoice and push it to Fasah Pay.
     *
     * @param  array<string, mixed>  $metaOverride
     */
    public function syncDaftraInvoice(int|string $daftraInvoiceId, array $metaOverride = []): SyncedInvoice
    {
        $existing = SyncedInvoice::query()
            ->where('daftra_invoice_id', $daftraInvoiceId)
            ->first();

        if ($existing && $existing->status === SyncedInvoice::STATUS_SYNCED && empty($metaOverride['force'])) {
            return $existing;
        }

        $daftraPayload = $this->daftra->getInvoice($daftraInvoiceId);
        $built = $this->builder->build($daftraPayload, $metaOverride);

        $record = SyncedInvoice::query()->updateOrCreate(
            ['daftra_invoice_id' => (int) $daftraInvoiceId],
            [
                'daftra_invoice_number' => $built['internal_invoice_number'],
                'internal_invoice_number' => $built['internal_invoice_number'],
                'invoice_type' => $built['type'],
                'grand_total' => $built['grand_total'],
                'total_vat' => $built['total_vat'],
                'logistics_meta' => $built['meta'],
                'request_payload' => $built['payload'],
                'status' => SyncedInvoice::STATUS_PENDING,
                'last_error' => null,
            ]
        );

        try {
            $response = $this->fasah->createInvoice($built['type'], $built['payload']);

            $record->fill([
                'fasah_invoice_number' => $response['fasahpayInvoiceNumber']
                    ?? $response['FasahPayInvoiceNumber']
                    ?? null,
                'fasah_response' => $response,
                'status' => SyncedInvoice::STATUS_SYNCED,
                'fasah_status' => $response['statusCode'] ?? '00000',
                'synced_at' => now(),
                'last_error' => null,
            ])->save();

            Log::info('Invoice synced to Fasah Pay', [
                'daftra_invoice_id' => $daftraInvoiceId,
                'fasah_invoice_number' => $record->fasah_invoice_number,
            ]);

            return $record->fresh();
        } catch (Throwable $e) {
            $record->fill([
                'status' => SyncedInvoice::STATUS_FAILED,
                'last_error' => $e->getMessage(),
            ])->save();

            throw $e;
        }
    }

    /**
     * Handle invoice status callback from Fasah Pay.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleInvoiceNotification(array $payload): SyncedInvoice
    {
        $fasahNo = (string) ($payload['fasahpayInvoiceNumber']
            ?? $payload['FasahPayInvoiceNumber']
            ?? '');
        $internalNo = (string) ($payload['internalInvoiceNumber'] ?? '');
        $status = strtoupper((string) ($payload['invoiceStatus'] ?? ''));
        $sadad = (string) ($payload['sadadNumber'] ?? '');

        $record = SyncedInvoice::query()
            ->when($fasahNo !== '', fn ($q) => $q->where('fasah_invoice_number', $fasahNo))
            ->when($fasahNo === '' && $internalNo !== '', fn ($q) => $q->where('internal_invoice_number', $internalNo))
            ->first();

        if (! $record) {
            throw new \RuntimeException(
                "Synced invoice not found for Fasah callback (fasah={$fasahNo}, internal={$internalNo})"
            );
        }

        $record->sadad_number = $sadad ?: $record->sadad_number;
        $record->fasah_status = $status;

        if ($status === 'PAID') {
            $record->status = SyncedInvoice::STATUS_PAID;
            $record->paid_at = now();

            if (! $record->daftra_marked_paid) {
                $this->markPaidInDaftra($record, $sadad);
            }
        } elseif ($status === 'CAN') {
            $record->status = SyncedInvoice::STATUS_CANCELLED;
        } elseif ($status === 'WAITING') {
            $record->status = SyncedInvoice::STATUS_WAITING;
        } elseif ($status === 'UNPAID' && $record->status === SyncedInvoice::STATUS_SYNCED) {
            // keep synced, store sadad if present
        }

        $record->save();

        return $record;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleSettlementNotification(array $payload): ?SyncedInvoice
    {
        $fasahNo = (string) ($payload['invoiceNumber'] ?? $payload['fasahpayInvoiceNumber'] ?? '');
        $internalNo = (string) ($payload['internalInvoiceNumber'] ?? '');

        $record = SyncedInvoice::query()
            ->when($fasahNo !== '', fn ($q) => $q->where('fasah_invoice_number', $fasahNo))
            ->when($fasahNo === '' && $internalNo !== '', fn ($q) => $q->where('internal_invoice_number', $internalNo))
            ->first();

        if (! $record) {
            return null;
        }

        $record->fill([
            'status' => SyncedInvoice::STATUS_SETTLED,
            'settled_at' => now(),
            'sadad_number' => $payload['sadadNumber'] ?? $record->sadad_number,
        ])->save();

        return $record;
    }

    private function markPaidInDaftra(SyncedInvoice $record, string $sadadNumber): void
    {
        try {
            DB::transaction(function () use ($record, $sadadNumber) {
                $amount = (float) ($record->grand_total ?? 0);
                if ($amount <= 0) {
                    throw new \RuntimeException('Cannot mark Daftra paid: grand_total is empty');
                }

                $this->daftra->addInvoicePayment(
                    $record->daftra_invoice_id,
                    $amount,
                    $sadadNumber !== '' ? $sadadNumber : (string) $record->fasah_invoice_number,
                );

                $record->daftra_marked_paid = true;
                $record->save();
            });
        } catch (Throwable $e) {
            Log::error('Failed to mark Daftra invoice paid', [
                'daftra_invoice_id' => $record->daftra_invoice_id,
                'error' => $e->getMessage(),
            ]);
            $record->last_error = 'Daftra payment mark failed: '.$e->getMessage();
            $record->save();
        }
    }
}
