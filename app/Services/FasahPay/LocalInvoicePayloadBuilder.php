<?php

namespace App\Services\FasahPay;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Validation\ValidationException;

class LocalInvoicePayloadBuilder
{
    public const TYPES = ['general', 'billoflading', 'declaration', 'custombroker', 'importer', 'shippingagent'];

    public function __construct(private readonly InvoicePayloadBuilder $builder) {}

    public function validateForIssue(array $data, Client $client, float $total, float $paidDeposit): void
    {
        $type = (string) ($data['fasah_type'] ?? '');
        $meta = $data['fasah_meta'] ?? [];
        if (! in_array($type, self::TYPES, true)) {
            throw ValidationException::withMessages(['fasah_type' => 'اختر نوع فاتورة Fasah.']);
        }

        $required = match ($type) {
            'billoflading' => ['bill_of_lading', 'shipment_type', 'port'],
            'declaration' => ['declaration_number', 'declaration_date', 'declaration_type', 'port'],
            'custombroker' => ['customs_broker_license', 'license_type', 'port'],
            'importer' => ['importer_number', 'port'],
            'shippingagent' => ['shipping_agent_number', 'port'],
            default => [],
        };
        foreach ($required as $field) {
            if (trim((string) ($meta[$field] ?? '')) === '') {
                throw ValidationException::withMessages(["fasah_meta.{$field}" => "حقل Fasah مطلوب: {$field}."]);
            }
        }
        if ($type === 'billoflading' && empty($meta['doc_ref_no']) && (empty($meta['carrier_manifest']) || empty($meta['carrier_manifest_date']))) {
            throw ValidationException::withMessages(['fasah_meta.doc_ref_no' => 'أدخل مرجع المستند أو رقم وتاريخ بيان الناقل.']);
        }
        foreach (['shipment_type', 'declaration_type'] as $field) {
            if (isset($meta[$field]) && $meta[$field] !== '' && (! ctype_digit((string) $meta[$field]) || (int) $meta[$field] <= 0)) {
                throw ValidationException::withMessages(["fasah_meta.{$field}" => 'اختر رمز نوع صالحًا من Fasah.']);
            }
        }
        foreach (['carrier_manifest_date', 'declaration_date'] as $field) {
            if (! empty($meta[$field])) {
                $date = (string) $meta[$field];
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
                    throw ValidationException::withMessages(["fasah_meta.{$field}" => 'أدخل تاريخًا صحيحًا.']);
                }
            }
        }
        if ($type === 'general' && (empty($client->email) || empty($client->mobile ?: $client->phone))) {
            throw ValidationException::withMessages(['client_id' => 'الفاتورة العامة تحتاج بريد العميل وجواله أو هاتفه.']);
        }
        if ($total - $paidDeposit <= 0) {
            throw ValidationException::withMessages(['deposit_amount' => 'يجب أن يكون للفاتورة رصيد مستحق لإرسالها إلى Fasah.']);
        }
    }

    public function build(Invoice $invoice): array
    {
        $invoice->loadMissing('client', 'items');
        $balance = $invoice->balance;
        if ($balance <= 0) {
            throw new \RuntimeException('لا يوجد رصيد مستحق لإرساله إلى Fasah.');
        }

        $detailed = (float) $invoice->amount_paid === 0.0
            && (float) $invoice->discount_value === 0.0
            && (float) $invoice->adjustment === 0.0
            && (float) $invoice->insurance_expenses === 0.0;

        $balanceVat = (float) $invoice->total > 0
            ? round(min($balance, (float) $invoice->tax_total * $balance / (float) $invoice->total), 2)
            : 0;

        $items = $detailed
            ? $invoice->items->map(function ($item) {
                $net = max(0, round((float) $item->unit_price * (float) $item->quantity - (float) $item->discount, 2));
                return ['InvoiceItem' => [
                    'id' => $item->id,
                    'description' => $item->name.' '.($item->description ?? ''),
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount' => (float) $item->discount,
                    'discount_type' => 2,
                    'tax1_value' => round((float) $item->line_total - $net, 2),
                ]];
            })->all()
            : [['InvoiceItem' => [
                'id' => $invoice->id,
                'description' => 'Outstanding balance for local invoice #'.$invoice->number,
                'quantity' => 1,
                'unit_price' => round($balance - $balanceVat, 2),
                'tax1_value' => $balanceVat,
            ]]];

        $issueDate = $invoice->issue_date ?? $invoice->invoice_date;
        $dueDate = $issueDate->copy()->addDays((int) $invoice->payment_terms);
        $source = [
            'Invoice' => [
                'id' => $invoice->id,
                'no' => 'LOCAL-'.$invoice->number,
                'date' => $issueDate->format('Y-m-d'),
                'due_date' => $dueDate->format('Y-m-d'),
                'notes' => 'Local invoice #'.$invoice->number.'; original total SAR '.$invoice->total.'; payable SAR '.number_format($balance, 2, '.', ''),
                'Client' => [
                    'business_name' => $invoice->client?->business_name,
                    'email' => $invoice->client?->email,
                    'phone1' => $invoice->client?->mobile ?: $invoice->client?->phone,
                ],
            ],
            'InvoiceItem' => $items,
        ];
        $built = $this->builder->build($source, array_merge($invoice->fasah_meta ?? [], ['invoice_type' => $invoice->fasah_type]));
        // Fasah collects only the outstanding amount. For a partly paid or adjusted
        // invoice, preserve the server-calculated VAT share in the balance line.
        if (abs($built['grand_total'] - $balance) > 0.01) {
            throw new \RuntimeException('تعذر مطابقة رصيد الفاتورة مع مبلغ Fasah.');
        }

        return $built;
    }
}
