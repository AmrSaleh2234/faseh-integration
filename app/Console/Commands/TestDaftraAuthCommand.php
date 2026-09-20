<?php

namespace App\Console\Commands;

use App\Services\Daftra\DaftraClient;
use Illuminate\Console\Command;

class TestDaftraAuthCommand extends Command
{
    protected $signature = 'daftra:test-auth {--limit=5 : How many invoices to list}';

    protected $description = 'Test Daftra API connectivity by listing recent invoices';

    public function handle(DaftraClient $daftra): int
    {
        try {
            $result = $daftra->listInvoices(1, (int) $this->option('limit'));
        } catch (\Throwable $e) {
            $this->error('Daftra connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $invoices = $result['data'] ?? $result;

        if (empty($invoices)) {
            $this->info('Connected to Daftra successfully, but no invoices were returned.');

            return self::SUCCESS;
        }

        $rows = collect($invoices)->map(function ($item) {
            $invoice = $item['Invoice'] ?? $item;

            return [
                $invoice['id'] ?? '-',
                $invoice['no'] ?? $invoice['number'] ?? '-',
                $invoice['client_id'] ?? '-',
                $invoice['summary_total'] ?? $invoice['total'] ?? '-',
                $invoice['is_paid'] ?? $invoice['status'] ?? '-',
            ];
        })->all();

        $this->info('Connected to Daftra successfully.');
        $this->table(['ID', 'Number', 'Client ID', 'Total', 'Status'], $rows);

        return self::SUCCESS;
    }
}
