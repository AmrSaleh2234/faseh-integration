<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\User;
use App\Jobs\SyncLocalInvoiceToFasahJob;
use App\Services\FasahPay\LocalInvoicePayloadBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveInvoice
{
    public function __construct(private RecordActivity $activity, private LocalInvoicePayloadBuilder $fasahBuilder) {}

    private function cents(mixed $value): int { return (int) round(((float) ($value ?: 0)) * 100); }
    private function amount(int $cents): string { return number_format($cents / 100, 2, '.', ''); }

    public function update(Invoice $invoice, array $data, User $actor): Invoice
    {
        if ($invoice->status === 'draft') return $this->save($data, $invoice, $actor);
        return $this->updateMetadata($invoice, $data, $actor);
    }

    public function save(array $data, ?Invoice $invoice, User $actor): Invoice
    {
        if (($data['action'] ?? '') === 'metadata') throw ValidationException::withMessages(['action' => 'إجراء غير صالح للمسودة.']);
        if ($invoice && $invoice->status !== 'draft') throw ValidationException::withMessages(['invoice' => 'لا يمكن تعديل البيانات المالية بعد إصدار الفاتورة.']);
        $issue = ($data['action'] ?? '') === 'issue';
        return DB::transaction(function () use ($data, $invoice, $actor, $issue) {
            $invoice ??= new Invoice(['created_by' => $actor->id]);
            $items = array_values(array_filter($data['items'] ?? [], fn ($item) => trim($item['name'] ?? '') !== ''));
            if ($issue && (empty($data['client_id']) || !$items)) throw ValidationException::withMessages(['items' => 'اختر العميل وأضف بندًا واحدًا على الأقل.']);
            if ($issue && (!Client::whereKey($data['client_id'])->exists() || empty($data['invoice_date']) || empty($data['issue_date']))) throw ValidationException::withMessages(['client_id' => 'اختر عميلًا نشطًا وتاريخي الفاتورة والإصدار.']);
            $subtotal = 0; $taxTotal = 0; $rows = [];
            foreach ($items as $position => $item) {
                $price = $this->cents($item['unit_price'] ?? 0);
                $quantity = (float) ($item['quantity'] ?? 0);
                $base = (int) round($price * $quantity);
                $discount = $this->cents($item['discount'] ?? 0);
                if ($issue && ($quantity <= 0 || $discount > $base)) throw ValidationException::withMessages(['items' => 'تحقق من الكمية والخصم في البنود.']);
                $net = max(0, $base - $discount);
                $taxOne = (float) ($item['tax_one'] ?? 0);
                $taxTwo = (float) ($item['tax_two'] ?? 0);
                $tax = (int) round($net * ($taxOne + $taxTwo) / 100);
                $subtotal += $net + $tax; $taxTotal += $tax;
                $rows[] = ['position' => $position, 'name' => $item['name'], 'description' => $item['description'] ?? null, 'unit_price' => $this->amount($price), 'quantity' => $quantity, 'discount' => $this->amount($discount), 'tax_one' => $taxOne, 'tax_two' => $taxTwo, 'line_total' => $this->amount($net + $tax)];
            }
            $discountValue = $this->cents($data['discount_value'] ?? 0);
            if (($data['discount_kind'] ?? 'amount') === 'percent' && $discountValue > 10000) throw ValidationException::withMessages(['discount_value' => 'نسبة الخصم لا تتجاوز 100%.']);
            $deduction = ($data['discount_kind'] ?? 'amount') === 'percent' ? (int) round($subtotal * $discountValue / 10000) : $discountValue;
            $total = max(0, $subtotal - $deduction + $this->cents($data['adjustment'] ?? 0) + $this->cents($data['insurance_expenses'] ?? 0));
            if ($issue && $total <= 0) throw ValidationException::withMessages(['items' => 'إجمالي الفاتورة يجب أن يكون أكبر من صفر.']);
            $deposit = $this->cents($data['deposit_amount'] ?? 0);
            if (($data['deposit_kind'] ?? 'amount') === 'percent' && $deposit > 10000) throw ValidationException::withMessages(['deposit_amount' => 'نسبة الدفعة لا تتجاوز 100%.']);
            if (($data['deposit_kind'] ?? 'amount') === 'percent') $deposit = (int) round($total * $deposit / 10000);
            if ($issue && $deposit > $total) throw ValidationException::withMessages(['deposit_amount' => 'الدفعة المقدمة أكبر من إجمالي الفاتورة.']);
            if ($issue) {
                $this->fasahBuilder->validateForIssue(
                    $data,
                    Client::findOrFail($data['client_id']),
                    $total / 100,
                    ! empty($data['deposit_paid']) ? $deposit / 100 : 0,
                );
            }
            $invoice->fill(collect($data)->only(['client_id', 'sales_staff_id', 'invoice_date', 'issue_date', 'payment_terms', 'delivery_method', 'discount_kind', 'deposit_kind', 'notes'])->all());
            $invoice->fasah_type = $data['fasah_type'] ?? null;
            $invoice->fasah_meta = $data['fasah_meta'] ?? [];
            $invoice->fill(['currency' => 'SAR', 'discount_value' => $this->amount($discountValue), 'adjustment' => $this->amount($this->cents($data['adjustment'] ?? 0)), 'deposit_amount' => $this->amount($this->cents($data['deposit_amount'] ?? 0)), 'deposit_paid' => (bool) ($data['deposit_paid'] ?? false), 'insurance_visible' => (bool) ($data['insurance_visible'] ?? false), 'insurance_expenses' => $this->amount($this->cents($data['insurance_expenses'] ?? 0)), 'subtotal' => $this->amount($subtotal), 'tax_total' => $this->amount($taxTotal), 'total' => $this->amount($total)]);
            if ($issue) {
                $sequence = DB::table('invoice_sequences')->where('id', 1)->lockForUpdate()->first();
                $invoice->number = $sequence->next_number;
                DB::table('invoice_sequences')->where('id', 1)->update(['next_number' => $sequence->next_number + 1]);
                $invoice->status = 'issued';
                $invoice->issued_at = now();
            }
            $invoice->save();
            $invoice->items()->delete();
            foreach ($rows as $row) $invoice->items()->create($row);
            $invoice->customFields()->delete();
            foreach (array_values($data['custom_fields'] ?? []) as $position => $field) {
                if (trim($field['name'] ?? '') !== '') $invoice->customFields()->create(['position' => $position, 'name' => $field['name'], 'value' => $field['value'] ?? null]);
            }
            if ($issue && $invoice->deposit_paid && $deposit > 0) {
                $invoice->payments()->create(['recorded_by' => $actor->id, 'amount' => $this->amount($deposit), 'paid_on' => $invoice->issue_date ?: now()->toDateString(), 'method' => 'دفعة مقدمة']);
                $invoice->amount_paid = $this->amount($deposit);
                $invoice->save();
            }
            if ($issue) {
                $invoice->fasahSync()->create([
                    'internal_invoice_number' => 'LOCAL-'.$invoice->number,
                    'status' => 'pending',
                ]);
                DB::afterCommit(fn () => SyncLocalInvoiceToFasahJob::dispatch($invoice->id));
            }
            $this->activity->record($actor, $issue ? 'invoice.issued' : 'invoice.drafted', 'invoice', $invoice->id, 'فاتورة #'.($invoice->number ?? $invoice->id));
            return $invoice->load('items', 'customFields');
        });
    }

    public function updateMetadata(Invoice $invoice, array $data, User $actor): Invoice
    {
        if ($invoice->status === 'draft') throw ValidationException::withMessages(['invoice' => 'يمكن تعديل المسودة من نموذج الإنشاء.']);
        return DB::transaction(function () use ($invoice, $data, $actor) {
            $invoice->fill(collect($data)->only(['sales_staff_id', 'payment_terms', 'insurance_company', 'notes'])->all());
            if (array_key_exists('fasah_type', $data)) {
                if ($invoice->fasahSync?->status !== 'failed') {
                    throw ValidationException::withMessages(['fasah_type' => 'يمكن تعديل بيانات Fasah فقط بعد فشل المزامنة.']);
                }
                $this->fasahBuilder->validateForIssue($data, $invoice->client, (float) $invoice->total, (float) $invoice->amount_paid);
                $invoice->fasah_type = $data['fasah_type'];
                $invoice->fasah_meta = $data['fasah_meta'] ?? [];
            }
            $invoice->save();
            if (array_key_exists('custom_fields', $data)) {
                $invoice->customFields()->delete();
                foreach ($data['custom_fields'] as $position => $field) if (trim($field['name'] ?? '') !== '') $invoice->customFields()->create(['position' => $position, 'name' => $field['name'], 'value' => $field['value'] ?? null]);
            }
            $this->activity->record($actor, 'invoice.updated', 'invoice', $invoice->id, 'فاتورة #'.$invoice->number);
            return $invoice;
        });
    }

    public function deleteDraft(Invoice $invoice, User $actor): void
    {
        if ($invoice->status !== 'draft') throw ValidationException::withMessages(['invoice' => 'لا يمكن حذف فاتورة صادرة.']);
        DB::transaction(function () use ($invoice, $actor) {
            $this->activity->record($actor, 'invoice.deleted', 'invoice', $invoice->id, 'مسودة #'.$invoice->id);
            $invoice->delete();
        });
    }
}
