<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Daftra API
    |--------------------------------------------------------------------------
    | Base URL example: https://yourcompany.daftra.com/api2
    | API key: Settings → API Keys inside Daftra.
    */
    'base_url' => rtrim(env('DAFTRA_BASE_URL', ''), '/'),
    'api_key' => env('DAFTRA_API_KEY'),
    'timeout' => (int) env('DAFTRA_TIMEOUT', 30),

    /*
    | Payment method key configured in Daftra (cash, bank, or custom).
    | Used when marking invoices paid after Fasah Pay callback.
    */
    'payment_method' => env('DAFTRA_PAYMENT_METHOD', 'bank'),

    /*
    | Custom field keys on Daftra invoices that carry Fasah logistics data.
    | Create matching custom fields in Daftra and put the KEY (not label) here.
    */
    'custom_fields' => [
        'invoice_type' => env('DAFTRA_CF_INVOICE_TYPE', 'fasah_invoice_type'),
        'bill_of_lading' => env('DAFTRA_CF_BILL_OF_LADING', 'bill_of_lading'),
        'doc_ref_no' => env('DAFTRA_CF_DOC_REF_NO', 'doc_ref_no'),
        'carrier_manifest' => env('DAFTRA_CF_CARRIER_MANIFEST', 'carrier_manifest'),
        'carrier_manifest_date' => env('DAFTRA_CF_CARRIER_MANIFEST_DATE', 'carrier_manifest_date'),
        'shipment_type' => env('DAFTRA_CF_SHIPMENT_TYPE', 'shipment_type'),
        'port' => env('DAFTRA_CF_PORT', 'port'),
        'declaration_number' => env('DAFTRA_CF_DECLARATION_NUMBER', 'declaration_number'),
        'declaration_date' => env('DAFTRA_CF_DECLARATION_DATE', 'declaration_date'),
        'declaration_type' => env('DAFTRA_CF_DECLARATION_TYPE', 'declaration_type'),
        'importer_number' => env('DAFTRA_CF_IMPORTER_NUMBER', 'importer_number'),
        'customs_broker_license' => env('DAFTRA_CF_CUSTOMS_BROKER_LICENSE', 'customs_broker_license'),
        'license_type' => env('DAFTRA_CF_LICENSE_TYPE', 'license_type'),
        'shipping_agent_number' => env('DAFTRA_CF_SHIPPING_AGENT_NUMBER', 'shipping_agent_number'),
        'customer_vat' => env('DAFTRA_CF_CUSTOMER_VAT', 'customer_vat'),
    ],
];
