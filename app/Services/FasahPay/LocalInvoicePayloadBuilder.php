<?php

namespace App\Services\FasahPay;

use App\Models\Client;
use App\Models\Invoice;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class LocalInvoicePayloadBuilder
{
    public const TYPES = ['general', 'billoflading', 'declaration', 'custombroker', 'importer', 'shippingagent'];

    public function __construct(private readonly InvoicePayloadBuilder $builder, private readonly ClientReadiness $readiness) {}

    public function validateForIssue(array $data, Client $client, float $total, float $paidDeposit, float $taxTotal = 0): void
    {
        $type = (string) ($data['fasah_type'] ?? '');
        $meta = $data['fasah_meta'] ?? [];
        $errors = [];
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
        $labels = [
            'bill_of_lading' => 'رقم بوليصة الشحن', 'shipment_type' => 'نوع الشحنة', 'port' => 'رمز الميناء',
            'declaration_number' => 'رقم البيان الجمركي', 'declaration_date' => 'تاريخ البيان الجمركي',
            'declaration_type' => 'نوع البيان الجمركي', 'customs_broker_license' => 'رخصة المخلص الجمركي',
            'license_type' => 'نوع الرخصة', 'importer_number' => 'رقم المستورد', 'shipping_agent_number' => 'رقم وكيل الشحن',
        ];
        foreach ($required as $field) {
            if (trim((string) ($meta[$field] ?? '')) === '') {
                $errors["fasah_meta.{$field}"] = "أدخل {$labels[$field]}.";
            }
        }
        if ($type === 'billoflading' && empty($meta['doc_ref_no']) && (empty($meta['carrier_manifest']) || empty($meta['carrier_manifest_date']))) {
            $errors['fasah_meta.doc_ref_no'] = 'أدخل مرجع المستند أو رقم وتاريخ بيان الناقل.';
        }
        if (! empty($meta['port']) && ! ctype_digit((string) $meta['port'])) {
            $errors['fasah_meta.port'] = 'رمز الميناء يجب أن يتكون من أرقام فقط.';
        }
        if ($type === 'billoflading' && ! empty($meta['shipment_type']) && (! ctype_digit((string) $meta['shipment_type']) || ! in_array((int) $meta['shipment_type'], FasahLookupValues::CONFIRMED_TYPES, true))) {
            $errors['fasah_meta.shipment_type'] = 'اختر نوع شحنة من رموز Fasah المعتمدة.';
        }
        if ($type === 'declaration' && ! empty($meta['declaration_type']) && (! ctype_digit((string) $meta['declaration_type']) || ! in_array((int) $meta['declaration_type'], [...FasahLookupValues::CONFIRMED_TYPES, ...FasahLookupValues::DISPUTED_DECLARATION_TYPES], true))) {
            $errors['fasah_meta.declaration_type'] = 'اختر نوع بيان من الرموز الموضحة في Fasah.';
        }
        if ($type === 'custombroker' && ! empty($meta['license_type']) && ! in_array((string) $meta['license_type'], ['1', '2', '5'], true)) {
            $errors['fasah_meta.license_type'] = 'نوع الرخصة يجب أن يكون 1 أو 2 أو 5.';
        }
        foreach (['carrier_manifest_date', 'declaration_date'] as $field) {
            if (! empty($meta[$field])) {
                $date = (string) $meta[$field];
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
                    $errors["fasah_meta.{$field}"] = 'أدخل تاريخًا صحيحًا بصيغة سنة-شهر-يوم.';
                }
            }
        }
        if ($type === 'general') {
            foreach ($this->readiness->issues($client) as $message) {
                $errors['client_id'][] = $message;
            }
        } elseif ($client->tax_number && preg_match('/^[0-9]{15}$/D', trim($client->tax_number)) !== 1) {
            $errors['client_id'] = 'رقم ضريبة العميل المُدخل غير صالح لـ Fasah؛ صححه إلى 15 رقمًا أو احذفه.';
        }
        $issueDate = $data['issue_date'] ?? null;
        if ($issueDate) {
            $issue = CarbonImmutable::parse($issueDate, 'Asia/Riyadh')->startOfDay();
            $today = CarbonImmutable::now('Asia/Riyadh')->startOfDay();
            if ($issue->greaterThan($today)) {
                $errors['issue_date'] = 'تاريخ الإصدار لا يمكن أن يكون في المستقبل بتوقيت السعودية.';
            }
            if ($issue->addDays((int) ($data['payment_terms'] ?? 0))->lessThan($today)) {
                $errors['payment_terms'] = 'تاريخ الاستحقاق انتهى؛ زِد عدد أيام شروط الدفع قبل الإرسال إلى Fasah.';
            }
        }
        foreach ($data['items'] ?? [] as $index => $item) {
            if (trim((string) ($item['name'] ?? '')) === '') {
                continue;
            }
            if ((float) ($item['tax_one'] ?? 0) + (float) ($item['tax_two'] ?? 0) > 15) {
                $errors["items.{$index}.tax_two"] = 'ضريبة البند رقم '.($index + 1).' لا يمكن أن تتجاوز 15% في Fasah.';
            }
        }
        if (! in_array(config('fasahpay.payment_method', 'Sadad'), ['Sadad', 'View'], true)) {
            $errors['fasah_type'] = 'طريقة الدفع المضبوطة في إعدادات Fasah غير صالحة؛ تواصل مع مسؤول النظام.';
        }
        if ($total - $paidDeposit <= 0) {
            $errors['deposit_amount'] = 'يجب أن يكون للفاتورة رصيد مستحق لإرسالها إلى Fasah.';
        }
        $usesBalanceLine = $paidDeposit > 0 || (float) ($data['discount_value'] ?? 0) > 0
            || (float) ($data['adjustment'] ?? 0) !== 0.0 || (float) ($data['insurance_expenses'] ?? 0) > 0;
        if ($usesBalanceLine && $taxTotal > 0 && $total > 0) {
            $balance = $total - $paidDeposit;
            $balanceVat = round(min($balance, $taxTotal * $balance / $total), 2);
            if (round($balanceVat / 0.15, 2) + $balanceVat > $balance + 0.02) {
                $errors['discount_value'] = 'الخصم أو التسوية يجعل مبلغ الضريبة أعلى من نسبة Fasah المسموحة. عدّل المبلغ أو البنود قبل الإصدار.';
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function validateForSend(Invoice $invoice): array
    {
        $invoice->loadMissing('client', 'items');
        $data = [
            'fasah_type' => $invoice->fasah_type,
            'fasah_meta' => $invoice->fasah_meta ?? [],
            'issue_date' => ($invoice->issue_date ?? $invoice->invoice_date)?->toDateString(),
            'payment_terms' => $invoice->payment_terms,
            'items' => $invoice->items->map(fn ($item) => [
                'name' => $item->name, 'tax_one' => $item->tax_one, 'tax_two' => $item->tax_two,
            ])->all(),
        ];
        $this->validateForIssue($data + [
            'discount_value' => $invoice->discount_value,
            'adjustment' => $invoice->adjustment,
            'insurance_expenses' => $invoice->insurance_expenses,
        ], $invoice->client, (float) $invoice->total, (float) $invoice->amount_paid, (float) $invoice->tax_total);
        if (! preg_match('/^[0-9]{15}$/D', (string) config('fasahpay.biller_vat_number'))) {
            throw ValidationException::withMessages(['fasah_type' => 'رقم ضريبة المُصدر في إعدادات Fasah يجب أن يكون 15 رقمًا.']);
        }
        $built = $this->build($invoice);
        $payload = $built['payload'];
        $items = $payload['item'] ?? [];
        if ($items === [] || abs(array_sum(array_column($items, 'lineItemAmountAfterDiscount')) - (float) $payload['totalBeforeVAT']) > 0.02
            || abs(array_sum(array_column($items, 'lineItemTotalVAT')) - (float) $payload['totalVAT']) > 0.02
            || abs((float) $payload['totalBeforeVAT'] + (float) $payload['totalVAT'] - (float) $payload['grandTotal']) > 0.02) {
            throw ValidationException::withMessages(['items' => 'مجموع بنود الفاتورة أو الضريبة لا يطابق إجمالي طلب Fasah.']);
        }
        foreach ($items as $index => $item) {
            $beforeDiscount = round((float) $item['itemPrice'] * (float) $item['quantity'], 2);
            $afterDiscount = round($beforeDiscount - (float) $item['lineItemDiscountAmount'], 2);
            $vat = round($afterDiscount * (float) $item['lineItemVAT'] / 100, 2);
            $discount = round($beforeDiscount * (float) $item['lineItemDiscount'] / 100, 2);
            if (! in_array((float) $item['lineItemVAT'], [0.0, 15.0], true)
                || abs($beforeDiscount - (float) $item['lineItemAmount']) > 0.02
                || abs($discount - (float) $item['lineItemDiscountAmount']) > 0.02
                || abs($afterDiscount - (float) $item['lineItemAmountAfterDiscount']) > 0.02
                || abs($vat - (float) $item['lineItemTotalVAT']) > 0.02
                || abs($afterDiscount + $vat - (float) $item['lineItemTotal']) > 0.02) {
                throw ValidationException::withMessages(["items.{$index}.tax_one" => 'حساب السعر أو الخصم أو الضريبة في البند رقم '.($index + 1).' لا يطابق قواعد Fasah.']);
            }
        }

        return $built;
    }

    /** @return array<string, string> */
    public function warnings(array $meta, string $type): array
    {
        $warnings = [];
        $port = trim((string) ($meta['port'] ?? ''));
        if ($port !== '' && ctype_digit($port) && ! FasahLookupValues::isKnownPort($port)) {
            $warnings['port'] = 'رمز الميناء غير موجود في القائمة المرفقة؛ تأكد منه مع Fasah.';
        }
        if ($type === 'billoflading' && ! empty($meta['doc_ref_no']) && preg_match('/^[0-9]{14}$/D', (string) $meta['doc_ref_no']) !== 1) {
            $warnings['doc_ref_no'] = 'مرجع المستند عادةً 14 رقمًا؛ تأكد من قيمته.';
        }
        if ($type === 'declaration' && in_array((int) ($meta['declaration_type'] ?? 0), FasahLookupValues::DISPUTED_DECLARATION_TYPES, true)) {
            $warnings['declaration_type'] = 'هذا الرمز موجود في جدول القيم لكنه غير مذكور في تعريف API؛ تأكد منه مع Fasah.';
        }

        return $warnings;
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
                    'tax_rate' => (float) $item->tax_one + (float) $item->tax_two,
                ]];
            })->all()
            : $this->balanceItems($invoice, $balance, $balanceVat);

        $issueDate = $invoice->issue_date ?? $invoice->invoice_date;
        $dueDate = $issueDate->copy()->addDays((int) $invoice->payment_terms);
        $client = $invoice->client;
        $clientPhone = (string) ($client?->mobile ?: $client?->phone);
        $clientPhone = preg_replace('/[\s().-]+/u', '', $clientPhone) ?? $clientPhone;
        $source = [
            'Invoice' => [
                'id' => $invoice->id,
                'no' => 'LOCAL-'.$invoice->number,
                'date' => $issueDate->format('Y-m-d'),
                'due_date' => $dueDate->format('Y-m-d'),
                'notes' => 'Local invoice #'.$invoice->number.'; original total SAR '.$invoice->total.'; payable SAR '.number_format($balance, 2, '.', ''),
                'Client' => [
                    'business_name' => $client?->business_name,
                    'email' => $client?->email,
                    'phone1' => $clientPhone,
                ],
            ],
            'InvoiceItem' => $items,
        ];
        $built = $this->builder->build($source, array_merge([
            'customer_vat' => $client?->tax_number,
            'company_registration_number' => $client?->registration,
        ], $invoice->fasah_meta ?? [], ['invoice_type' => $invoice->fasah_type]));
        // Fasah collects only the outstanding amount. For a partly paid or adjusted
        // invoice, preserve the server-calculated VAT share in the balance line.
        if (abs($built['grand_total'] - $balance) > 0.01) {
            throw new \RuntimeException('تعذر مطابقة رصيد الفاتورة مع مبلغ Fasah.');
        }

        return $built;
    }

    private function balanceItems(Invoice $invoice, float $balance, float $balanceVat): array
    {
        $taxableBase = $balanceVat > 0 ? round($balanceVat / 0.15, 2) : 0;
        $untaxedBase = round($balance - $balanceVat - $taxableBase, 2);
        if ($untaxedBase < -0.02) {
            throw ValidationException::withMessages(['discount_value' => 'تعذر تمثيل الضريبة بنسبة 15% بعد الخصم أو التسوية. عدّل المبالغ قبل الإرسال.']);
        }
        if ($untaxedBase < 0) {
            $taxableBase = round($taxableBase + $untaxedBase, 2);
            $untaxedBase = 0;
        }
        $items = [];
        if ($balanceVat > 0) {
            $items[] = ['InvoiceItem' => [
                'id' => $invoice->id,
                'description' => 'Taxable outstanding balance for local invoice #'.$invoice->number,
                'quantity' => 1,
                'unit_price' => $taxableBase,
                'tax1_value' => $balanceVat,
                'tax_rate' => 15,
            ]];
        }
        if ($untaxedBase > 0 || $items === []) {
            $items[] = ['InvoiceItem' => [
                'id' => $invoice->id.'-untaxed',
                'description' => 'Outstanding balance for local invoice #'.$invoice->number,
                'quantity' => 1,
                'unit_price' => max(0, $untaxedBase),
                'tax1_value' => 0,
                'tax_rate' => 0,
            ]];
        }

        return $items;
    }
}
