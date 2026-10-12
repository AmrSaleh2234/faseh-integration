<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordInvoicePayment
{
    public function __construct(private RecordActivity $activity) {}

    public function record(Invoice $invoice, array $data, User $actor): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $data, $actor) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $amount = (int) round((float) $data['amount'] * 100);
            $balance = (int) round(((float) $invoice->total - (float) $invoice->amount_paid) * 100);
            if ($invoice->status !== 'issued' || $amount <= 0 || $amount > $balance) throw ValidationException::withMessages(['amount' => 'المبلغ غير صالح أو يتجاوز الرصيد المستحق.']);
            $payment = $invoice->payments()->create(['recorded_by' => $actor->id, 'amount' => number_format($amount / 100, 2, '.', ''), 'paid_on' => $data['paid_on'], 'method' => $data['method'], 'reference' => $data['reference'] ?? null]);
            $invoice->amount_paid = number_format(((int) round((float) $invoice->amount_paid * 100) + $amount) / 100, 2, '.', '');
            $invoice->save();
            $this->activity->record($actor, 'invoice.paid', 'invoice', $invoice->id, 'فاتورة #'.$invoice->number, 'دفعة SAR '.number_format($amount / 100, 2));
            return $payment;
        });
    }
}
