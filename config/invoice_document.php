<?php

return [
    'company' => [
        'name' => env('INVOICE_COMPANY_NAME', 'Titanic ways'),
        'registration' => env('INVOICE_COMPANY_REGISTRATION', '4030157346'),
        'vat_number' => env('INVOICE_COMPANY_VAT_NUMBER') ?: env('FASAHPAY_BILLER_VAT_NUMBER'),
        'address_line_one' => env('INVOICE_COMPANY_ADDRESS_1', '7303 As Safa Street'),
        'address_line_two' => env('INVOICE_COMPANY_ADDRESS_2', 'Jeddah, Makkah AlMukarrama 22442'),
        'phone' => env('INVOICE_COMPANY_PHONE', '00966122060033'),
    ],
    'banks' => [
        'sar' => [
            'label' => 'BANK DETAILS (SAR ACCOUNT)',
            'account_name' => env('INVOICE_BANK_SAR_NAME', 'TITANIC WAYS COMPANY'),
            'iban' => env('INVOICE_BANK_SAR_IBAN'),
            'swift' => env('INVOICE_BANK_SAR_SWIFT'),
            'bank' => env('INVOICE_BANK_SAR_BANK'),
        ],
        'usd' => [
            'label' => 'BANK DETAILS (USD ACCOUNT)',
            'account_name' => env('INVOICE_BANK_USD_NAME', 'TITANIC WAYS COMPANY'),
            'iban' => env('INVOICE_BANK_USD_IBAN'),
            'swift' => env('INVOICE_BANK_USD_SWIFT'),
            'bank' => env('INVOICE_BANK_USD_BANK'),
        ],
    ],
];
