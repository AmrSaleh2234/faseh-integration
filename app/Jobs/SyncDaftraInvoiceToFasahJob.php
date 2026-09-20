<?php

namespace App\Jobs;

use App\Services\InvoiceSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncDaftraInvoiceToFasahJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    /**
     * @param  array<string, mixed>  $metaOverride
     */
    public function __construct(
        public readonly int $daftraInvoiceId,
        public readonly array $metaOverride = [],
    ) {}

    public function handle(InvoiceSyncService $sync): void
    {
        $sync->syncDaftraInvoice($this->daftraInvoiceId, $this->metaOverride);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SyncDaftraInvoiceToFasahJob failed', [
            'daftra_invoice_id' => $this->daftraInvoiceId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
