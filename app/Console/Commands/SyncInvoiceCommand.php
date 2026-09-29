<?php

namespace App\Console\Commands;

use App\Services\InvoiceSyncService;
use Illuminate\Console\Command;

class SyncInvoiceCommand extends Command
{
    protected $signature = 'fasah:sync-invoice
        {daftra_invoice_id : Daftra invoice ID}
        {--type= : Invoice type override (billoflading|declaration|custombroker|importer|shippingagent)}
        {--bl= : Bill of lading number}
        {--doc-ref= : Manifest doc ref (Option A)}
        {--manifest= : Carrier manifest number (Option B)}
        {--manifest-date= : Carrier manifest date Y-m-d}
        {--port= : Port code}
        {--shipment-type= : Shipment type lookup}
        {--consumer-email= : Consumer email override (required for --type=general if missing on Daftra client)}
        {--consumer-mobile= : Consumer mobile override (required for --type=general if missing on Daftra client)}
        {--company-name= : Company name override (for --type=general)}
        {--force : Re-sync even if already synced}';

    protected $description = 'Sync one Daftra invoice to Fasah Pay';

    public function handle(InvoiceSyncService $sync): int
    {
        $meta = array_filter([
            'invoice_type' => $this->option('type'),
            'bill_of_lading' => $this->option('bl'),
            'doc_ref_no' => $this->option('doc-ref'),
            'carrier_manifest' => $this->option('manifest'),
            'carrier_manifest_date' => $this->option('manifest-date'),
            'port' => $this->option('port'),
            'shipment_type' => $this->option('shipment-type'),
            'consumer_email' => $this->option('consumer-email'),
            'consumer_mobile' => $this->option('consumer-mobile'),
            'company_name_en' => $this->option('company-name'),
            'force' => $this->option('force'),
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $record = $sync->syncDaftraInvoice((int) $this->argument('daftra_invoice_id'), $meta);
            $this->info('Synced successfully');
            $this->table(
                ['Daftra ID', 'Internal No', 'Fasah No', 'Status'],
                [[
                    $record->daftra_invoice_id,
                    $record->internal_invoice_number,
                    $record->fasah_invoice_number,
                    $record->status,
                ]]
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
