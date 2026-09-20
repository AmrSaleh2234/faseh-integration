<?php

namespace App\Console\Commands;

use App\Services\FasahPay\FasahPayClient;
use Illuminate\Console\Command;

class SubscribeFasahCallbacksCommand extends Command
{
    protected $signature = 'fasah:subscribe-callbacks
        {--invoice-url= : Override invoice callback URL}
        {--settlement-url= : Override settlement callback URL}';

    protected $description = 'Register invoice + settlement callback URLs with Fasah Pay';

    public function handle(FasahPayClient $client): int
    {
        $invoiceUrl = $this->option('invoice-url') ?: config('fasahpay.invoice_callback_url');
        $settlementUrl = $this->option('settlement-url') ?: config('fasahpay.settlement_callback_url');

        if (! $invoiceUrl || ! $settlementUrl) {
            $this->error('Set FASAHPAY_INVOICE_CALLBACK_URL and FASAHPAY_SETTLEMENT_CALLBACK_URL in .env');

            return self::FAILURE;
        }

        try {
            $invoice = $client->subscribeInvoiceNotifications($invoiceUrl);
            $this->info('Invoice callback: '.json_encode($invoice));

            $settlement = $client->subscribeSettlementNotifications($settlementUrl);
            $this->info('Settlement callback: '.json_encode($settlement));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
