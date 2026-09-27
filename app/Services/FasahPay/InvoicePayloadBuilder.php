<?php

namespace App\Services\FasahPay;

use Carbon\Carbon;
use InvalidArgumentException;

class InvoicePayloadBuilder
{
    /**
     * Build Fasah Pay create-invoice payload from a normalized Daftra invoice + logistics meta.
     *
     * @param  array<string, mixed>  $daftraInvoice  Raw Daftra invoice API payload
     * @param  array<string, mixed>  $meta  Logistics fields (from custom fields or API override)
     * @return array{type: string, payload: array<string, mixed>}
     */
    public function build(array $daftraInvoice, array $meta = []): array
    {
        $daftraInvoice = $daftraInvoice['data'] ?? $daftraInvoice;
        $invoice = $daftraInvoice['Invoice'] ?? $daftraInvoice;
        $items = $daftraInvoice['InvoiceItem']
            ?? $daftraInvoice['InvoiceItems']
            ?? $invoice['InvoiceItem']
            ?? [];

        if (isset($items['InvoiceItem'])) {
            $items = $items['InvoiceItem'];
        }

        if (! is_array($items) || $items === []) {
            throw new InvalidArgumentException('Daftra invoice has no line items.');
        }

        // Normalize single item object to list
        if (isset($items['id']) || isset($items['unit_price']) || isset($items['quantity'])) {
            $items = [$items];
        }

        $custom = $this->extractCustomFields($daftraInvoice, $invoice);
        $meta = array_merge($custom, array_filter($meta, fn ($v) => $v !== null && $v !== ''));

        $type = strtolower((string) ($meta['invoice_type'] ?? config('fasahpay.default_invoice_type', 'billoflading')));

        $issueDate = $this->formatDate($invoice['date'] ?? $invoice['issue_date'] ?? now()->toDateString());
        $dueDate = $this->formatDate($invoice['due_date'] ?? $issueDate);

        $lineItems = $this->mapLineItems($items);
        $totals = $this->computeTotals($invoice, $lineItems);

        $header = [
            'billerVATNumber' => (string) config('fasahpay.biller_vat_number'),
            'customerVATNumber' => (string) ($meta['customer_vat'] ?? ''),
            'internalInvoiceNumber' => (string) ($invoice['no'] ?? $invoice['number'] ?? $invoice['id']),
            'invoiceDescription' => (string) ($invoice['notes'] ?? $invoice['summary'] ?? 'Invoice from Daftra'),
            'invoiceIssueDate' => $issueDate,
            'invoiceDueDate' => $dueDate,
            'paymentMethod' => (string) config('fasahpay.payment_method', 'Sadad'),
            'totalBeforeVAT' => $totals['totalBeforeVAT'],
            'totalVAT' => $totals['totalVAT'],
            'grandTotal' => $totals['grandTotal'],
            'item' => $lineItems,
        ];

        if (empty($header['billerVATNumber'])) {
            throw new InvalidArgumentException('FASAHPAY_BILLER_VAT_NUMBER is required in .env');
        }

        $payload = match ($type) {
            'general' => $this->general($header, $meta, $invoice),
            'billoflading', 'bill_of_lading', 'bl' => $this->billOfLading($header, $meta),
            'declaration' => $this->declaration($header, $meta),
            'custombroker', 'customs_broker', 'broker' => $this->customBroker($header, $meta),
            'importer' => $this->importer($header, $meta),
            'shippingagent', 'shipping_agent' => $this->shippingAgent($header, $meta),
            default => throw new InvalidArgumentException("Unsupported invoice type: {$type}"),
        };

        return [
            'type' => match ($type) {
                'bill_of_lading', 'bl' => 'billoflading',
                'customs_broker', 'broker' => 'custombroker',
                'shipping_agent' => 'shippingagent',
                default => $type,
            },
            'payload' => $payload,
            'meta' => $meta,
            'internal_invoice_number' => $header['internalInvoiceNumber'],
            'grand_total' => $header['grandTotal'],
            'total_vat' => $header['totalVAT'],
        ];
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function billOfLading(array $header, array $meta): array
    {
        $required = ['bill_of_lading', 'shipment_type', 'port'];
        $this->requireFields($meta, $required, 'billoflading');

        $payload = array_merge($header, [
            'billofLadingNo' => (string) $meta['bill_of_lading'],
            'port' => (string) $meta['port'],
            'shipmentType' => (int) $meta['shipment_type'],
        ]);

        if (! empty($meta['doc_ref_no'])) {
            $payload['docRefNo'] = (string) $meta['doc_ref_no'];
        } elseif (! empty($meta['carrier_manifest']) && ! empty($meta['carrier_manifest_date'])) {
            $payload['carrierManifest'] = (string) $meta['carrier_manifest'];
            $payload['carrierManifestDate'] = $this->formatDate($meta['carrier_manifest_date']);
        } else {
            throw new InvalidArgumentException(
                'billoflading requires either doc_ref_no (Option A) or carrier_manifest + carrier_manifest_date (Option B).'
            );
        }

        if (! empty($meta['company_name_en']) || ! empty($meta['consumer_email'])) {
            $payload['consumerContactDetails'] = array_filter([
                'companyNameEn' => $meta['company_name_en'] ?? null,
                'companyNameAr' => $meta['company_name_ar'] ?? null,
                'consumerEmail' => $meta['consumer_email'] ?? null,
                'consumerMobile' => $meta['consumer_mobile'] ?? null,
                'consumerUserId' => $meta['consumer_user_id'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $invoice
     * @return array<string, mixed>
     */
    private function general(array $header, array $meta, array $invoice): array
    {
        $client = $invoice['Client'] ?? [];

        $companyName = (string) ($meta['company_name_en']
            ?? $client['business_name']
            ?? trim(($client['first_name'] ?? '').' '.($client['last_name'] ?? ''))
            ?: 'N/A');
        $consumerEmail = (string) ($meta['consumer_email'] ?? $client['email'] ?? '');
        $consumerMobile = (string) ($meta['consumer_mobile'] ?? $client['phone2'] ?? $client['phone1'] ?? '');

        if ($consumerEmail === '' || $consumerMobile === '') {
            throw new InvalidArgumentException(
                'general invoice requires consumer_email and consumer_mobile (missing on Daftra Client and not passed via --bl-like meta overrides).'
            );
        }

        return array_merge($header, [
            'companyName' => $companyName,
            'companyRegistrationNumber' => (string) ($meta['company_registration_number'] ?? ''),
            'consumerEmail' => $consumerEmail,
            'consumerMobile' => $consumerMobile,
        ]);
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function declaration(array $header, array $meta): array
    {
        $this->requireFields($meta, ['declaration_number', 'declaration_date', 'declaration_type', 'port'], 'declaration');

        return array_merge($header, [
            'declarationNumber' => (string) $meta['declaration_number'],
            'declarationDate' => $this->formatDate($meta['declaration_date']),
            'declarationType' => (int) $meta['declaration_type'],
            'declarationPort' => (string) $meta['port'],
            'billofLadingNo' => (string) ($meta['bill_of_lading'] ?? ''),
        ]);
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function customBroker(array $header, array $meta): array
    {
        $this->requireFields($meta, ['customs_broker_license', 'license_type', 'port'], 'custombroker');

        return array_merge($header, [
            'customsBrokerLicenseNumber' => (string) $meta['customs_broker_license'],
            'licenseType' => (string) $meta['license_type'],
            'port' => (string) $meta['port'],
            'billofLadingNo' => (string) ($meta['bill_of_lading'] ?? ''),
        ]);
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function importer(array $header, array $meta): array
    {
        $this->requireFields($meta, ['importer_number', 'port'], 'importer');

        return array_merge($header, [
            'importerNumber' => (string) $meta['importer_number'],
            'port' => (string) $meta['port'],
            'billofLadingNo' => (string) ($meta['bill_of_lading'] ?? ''),
        ]);
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function shippingAgent(array $header, array $meta): array
    {
        $this->requireFields($meta, ['shipping_agent_number', 'port'], 'shippingagent');

        return array_merge($header, [
            'shippingAgentNumber' => (string) $meta['shipping_agent_number'],
            'port' => (string) $meta['port'],
            'billofLadingNo' => (string) ($meta['bill_of_lading'] ?? ''),
        ]);
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    private function mapLineItems(array $items): array
    {
        $mapped = [];
        $serial = 1;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $row = $item['InvoiceItem'] ?? $item;
            $qty = (float) ($row['quantity'] ?? 1);
            $price = (float) ($row['unit_price'] ?? $row['price'] ?? 0);
            $lineAmount = round($qty * $price, 2);
            $discountPct = (float) ($row['discount'] ?? 0);
            $discountType = (int) ($row['discount_type'] ?? 1); // 1 percent, 2 fixed
            $discountAmount = $discountType === 2
                ? round($discountPct, 2)
                : round($lineAmount * ($discountPct / 100), 2);
            $afterDiscount = round($lineAmount - $discountAmount, 2);

            $vatAmount = (float) ($row['tax1_value'] ?? $row['tax_value'] ?? $row['vat'] ?? 0);
            if ($vatAmount <= 0) {
                // Daftra's tax1/tax2 are tax rule IDs, not percentages.
                // summary_tax1/summary_tax2 hold the actual computed VAT amount.
                $vatAmount = round(
                    (float) ($row['summary_tax1'] ?? 0) + (float) ($row['summary_tax2'] ?? 0),
                    2
                );
            }

            $descEn = (string) ($row['description'] ?? $row['item'] ?? 'Item');
            $descAr = (string) ($row['description_ar'] ?? $row['arabic_description'] ?? $descEn);

            $mapped[] = [
                'invoiceLineSerialNo' => $serial++,
                'itemCode' => (string) ($row['product_id'] ?? $row['item_id'] ?? $serial - 1),
                'itemDescriptionArabic' => $descAr !== '' ? $descAr : $descEn,
                'itemDescriptionEnglish' => $descEn,
                'itemPrice' => $price,
                'quantity' => $qty,
                'lineItemAmount' => $lineAmount,
                'lineItemDiscount' => $discountType === 1 ? $discountPct : 0,
                'lineItemDiscountAmount' => $discountAmount,
                'lineItemAmountAfterDiscount' => $afterDiscount,
                'lineItemVAT' => $vatAmount > 0 ? 15 : 0,
                'lineItemTotalVAT' => $vatAmount,
                'lineItemTotal' => round($afterDiscount + $vatAmount, 2),
                'unitOfMeasureArabic' => (string) ($row['unit_ar'] ?? 'وحدة'),
                'unitOfMeasureEnglish' => (string) ($row['unit'] ?? 'unit'),
            ];
        }

        if ($mapped === []) {
            throw new InvalidArgumentException('No valid line items found on Daftra invoice.');
        }

        return $mapped;
    }

    /**
     * @param  array<string, mixed>  $invoice
     * @param  array<int, array<string, mixed>>  $lineItems
     * @return array{totalBeforeVAT: float, totalVAT: float, grandTotal: float}
     */
    private function computeTotals(array $invoice, array $lineItems): array
    {
        $before = array_sum(array_column($lineItems, 'lineItemAmountAfterDiscount'));
        $vat = array_sum(array_column($lineItems, 'lineItemTotalVAT'));

        // Prefer Daftra totals when present
        if (isset($invoice['subtotal']) || isset($invoice['summary_subtotal'])) {
            $before = (float) ($invoice['subtotal'] ?? $invoice['summary_subtotal']);
        }
        if (isset($invoice['tax_value']) || isset($invoice['summary_tax'])) {
            $vat = (float) ($invoice['tax_value'] ?? $invoice['summary_tax']);
        }

        $grand = isset($invoice['summary_total']) || isset($invoice['total'])
            ? (float) ($invoice['summary_total'] ?? $invoice['total'])
            : round($before + $vat, 2);

        return [
            'totalBeforeVAT' => round((float) $before, 2),
            'totalVAT' => round((float) $vat, 2),
            'grandTotal' => round((float) $grand, 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $daftraInvoice
     * @param  array<string, mixed>  $invoice
     * @return array<string, mixed>
     */
    private function extractCustomFields(array $daftraInvoice, array $invoice): array
    {
        $map = config('daftra.custom_fields', []);
        $rawFields = $daftraInvoice['CustomField']
            ?? $daftraInvoice['CustomFields']
            ?? $invoice['CustomField']
            ?? $invoice['custom_fields']
            ?? [];

        $flat = [];

        if (is_array($rawFields)) {
            // Shape A: [{key, value}] or [{name, value}]
            if (array_is_list($rawFields)) {
                foreach ($rawFields as $field) {
                    if (! is_array($field)) {
                        continue;
                    }
                    $key = $field['key'] ?? $field['name'] ?? $field['label'] ?? null;
                    $value = $field['value'] ?? $field['content'] ?? null;
                    if ($key !== null) {
                        $flat[(string) $key] = $value;
                    }
                }
            } else {
                // Shape B: { field_key: value }
                foreach ($rawFields as $key => $value) {
                    if (is_array($value) && array_key_exists('value', $value)) {
                        $flat[(string) $key] = $value['value'];
                    } else {
                        $flat[(string) $key] = $value;
                    }
                }
            }
        }

        $result = [];
        foreach ($map as $logical => $daftraKey) {
            if (array_key_exists($daftraKey, $flat) && $flat[$daftraKey] !== null && $flat[$daftraKey] !== '') {
                $result[$logical] = $flat[$daftraKey];
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<int, string>  $fields
     */
    private function requireFields(array $meta, array $fields, string $type): void
    {
        $missing = [];
        foreach ($fields as $field) {
            if (! isset($meta[$field]) || $meta[$field] === '') {
                $missing[] = $field;
            }
        }

        if ($missing !== []) {
            throw new InvalidArgumentException(
                "Missing required logistics fields for {$type}: ".implode(', ', $missing)
            );
        }
    }

    private function formatDate(mixed $value): string
    {
        return Carbon::parse((string) $value)->format('Y-m-d');
    }
}
