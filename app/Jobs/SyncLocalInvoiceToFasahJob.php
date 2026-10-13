<?php

namespace App\Jobs;

use App\Services\FasahPay\SyncLocalInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncLocalInvoiceToFasahJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public readonly int $invoiceId)
    {
        $this->onQueue('fasah-local');
    }

    public function handle(SyncLocalInvoice $sync): void
    {
        $sync->execute($this->invoiceId);
    }
}
