<?php

namespace App\Services\FasahPay;

use App\Models\LocalFasahSync;
use App\Models\Invoice;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncLocalInvoice
{
    public function __construct(
        private readonly LocalInvoicePayloadBuilder $builder,
        private readonly FasahPayClient $client,
    ) {}

    public function execute(int $invoiceId): LocalFasahSync
    {
        [$sync, $claimed] = DB::transaction(function () use ($invoiceId) {
            $sync = LocalFasahSync::where('invoice_id', $invoiceId)->lockForUpdate()->firstOrFail();
            if (! in_array($sync->status, ['pending', 'failed'], true)) {
                return [$sync, false];
            }
            $sync->update(['status' => 'sending', 'attempts' => $sync->attempts + 1, 'last_error' => null]);
            return [$sync, true];
        });

        if (! $claimed) {
            return $sync;
        }

        $requestStarted = false;
        try {
            $built = $this->builder->build($sync->invoice);
            $sync->update(['request_payload' => $built['payload']]);
            $requestStarted = true;
            $response = $this->client->createInvoice($built['type'], $built['payload']);
            $number = trim((string) ($response['fasahpayInvoiceNumber'] ?? $response['FasahPayInvoiceNumber'] ?? ''));
            if (($response['statusCode'] ?? null) !== '00000') {
                $sync->update([
                    'status' => 'failed',
                    'fasah_response' => $response,
                    'last_error' => 'رفض Fasah الفاتورة: '.mb_substr((string) ($response['statusDesc'] ?? $response['statusCode'] ?? 'رد غير معروف'), 0, 400),
                ]);
                return $sync->fresh();
            }
            if ($number === '') {
                $sync->update(['status' => 'uncertain', 'fasah_response' => $response, 'last_error' => 'لم يُرجع Fasah رقم فاتورة؛ يلزم التحقق قبل إعادة الإرسال.']);
                return $sync->fresh();
            }

            $sync->update([
                'status' => 'synced',
                'fasah_invoice_number' => $number,
                'fasah_status' => '00000',
                'fasah_response' => $response,
                'synced_at' => now(),
                'last_error' => null,
            ]);
        } catch (ConnectionException $e) {
            $sync->update(['status' => 'uncertain', 'last_error' => 'انقطع الاتصال؛ تحقق من Fasah قبل إعادة الإرسال.']);
        } catch (FasahPayRequestException $e) {
            $sync->update([
                'status' => ($e->httpStatus >= 500 || $e->httpStatus === 408) ? 'uncertain' : 'failed',
                'last_error' => ($e->httpStatus >= 500 || $e->httpStatus === 408)
                    ? 'نتيجة الطلب غير مؤكدة (HTTP '.$e->httpStatus.'). تحقق من Fasah قبل إعادة الإرسال.'
                    : 'رفض Fasah الطلب (HTTP '.$e->httpStatus.'). تحقق من بيانات الفاتورة.',
            ]);
        } catch (Throwable $e) {
            $definitelyLocal = str_contains($e->getMessage(), 'Missing Fasah Pay config');
            $sync->update([
                'status' => ($requestStarted && ! $definitelyLocal) ? 'uncertain' : 'failed',
                'last_error' => ($requestStarted && ! $definitelyLocal)
                    ? 'نتيجة إرسال الفاتورة غير مؤكدة. تحقق من Fasah قبل إعادة الإرسال.'
                    : $this->safeError($e),
            ]);
        }

        return $sync->fresh();
    }

    public function retry(LocalFasahSync $sync): void
    {
        $queued = DB::transaction(function () use ($sync) {
            $sync = LocalFasahSync::whereKey($sync->id)->lockForUpdate()->firstOrFail();
            if ($sync->status !== 'failed') {
                return false;
            }
            $sync->update(['status' => 'pending', 'last_error' => null]);
            return true;
        });
        if ($queued) {
            \App\Jobs\SyncLocalInvoiceToFasahJob::dispatch($sync->invoice_id)->afterCommit();
        }
    }

    public function retryInvoice(Invoice $invoice): void
    {
        $this->retry(LocalFasahSync::where('invoice_id', $invoice->id)->firstOrFail());
    }

    public function status(Invoice $invoice): array
    {
        $sync = LocalFasahSync::where('invoice_id', $invoice->id)->first();
        return [
            'status' => $sync?->status ?? 'not_started',
            'fasah_invoice_number' => $sync?->fasah_invoice_number,
            'message' => $sync?->last_error,
        ];
    }

    private function safeError(Throwable $e): string
    {
        if (str_contains($e->getMessage(), 'FASAHPAY_BILLER_VAT_NUMBER')) {
            return 'بيانات ربط Fasah ناقصة: رقم ضريبة المُصدر غير محدد.';
        }
        if (str_contains($e->getMessage(), 'Missing Fasah Pay config')) {
            return 'بيانات دخول Fasah غير مكتملة. راجع إعدادات sandbox.';
        }
        // Provider responses can include private details; keep the UI message bounded.
        return mb_substr($e->getMessage(), 0, 500);
    }
}
