<?php

namespace App\Http\Controllers;

use App\Actions\RecordInvoicePayment;
use App\Actions\SaveInvoice;
use App\Actions\StoreAttachment;
use App\Models\Invoice;
use App\Services\LocalReadService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request, LocalReadService $read) { return view('invoices.index', ['invoices' => $read->invoices($request)]); }
    public function create(LocalReadService $read) { return view('invoices.create', ['invoice' => null, 'clients' => $read->clientOptions(), 'salesPeople' => $read->salesOptions()]); }
    public function show(Invoice $invoice, LocalReadService $read) { return view('invoices.show', ['invoice' => $read->invoiceDetails($invoice)]); }
    public function edit(Invoice $invoice, LocalReadService $read)
    {
        return view($invoice->status === 'draft' ? 'invoices.create' : 'invoices.edit', ['invoice' => $read->invoiceForEditing($invoice), 'clients' => $read->clientOptions(), 'salesPeople' => $read->salesOptions()]);
    }
    public function store(Request $request, SaveInvoice $action, StoreAttachment $files)
    {
        $data = $this->validated($request);
        $invoice = $action->save($data, null, $request->user());
        foreach ($request->file('attachments', []) as $file) $files->store($invoice, $file, $request->user());
        return redirect()->route('invoices.show', $invoice)->with('status', 'تم حفظ الفاتورة.');
    }
    public function update(Request $request, Invoice $invoice, SaveInvoice $action, StoreAttachment $files)
    {
        $data = $this->validated($request);
        $action->update($invoice, $data, $request->user());
        foreach ($request->file('attachments', []) as $file) $files->store($invoice, $file, $request->user());
        return redirect()->route('invoices.show', $invoice)->with('status', 'تم تحديث الفاتورة.');
    }
    public function payment(Request $request, Invoice $invoice, RecordInvoicePayment $action)
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0'], 'paid_on' => ['required', 'date'], 'method' => ['required', 'string', 'max:100'], 'reference' => ['nullable', 'string', 'max:255']]);
        $action->record($invoice, $data, $request->user());
        return redirect()->route('invoices.show', $invoice)->with('status', 'تم تسجيل الدفعة.');
    }
    public function destroy(Request $request, Invoice $invoice, SaveInvoice $action)
    {
        $action->deleteDraft($invoice, $request->user());
        return redirect()->route('invoices.index')->with('status', 'تم حذف المسودة.');
    }
    private function validated(Request $request): array
    {
        return $request->validate([
            'action' => ['required', 'in:draft,issue,metadata'], 'client_id' => ['nullable', 'exists:clients,id'], 'sales_staff_id' => ['nullable', 'exists:staff,id'],
            'invoice_date' => ['nullable', 'date'], 'issue_date' => ['nullable', 'date'], 'payment_terms' => ['nullable', 'integer', 'min:0'],
            'delivery_method' => ['nullable', 'in:طباعة,بريد إلكتروني'], 'notes' => ['nullable', 'string'],
            'discount_value' => ['nullable', 'numeric', 'min:0'], 'discount_kind' => ['nullable', 'in:amount,percent'], 'adjustment' => ['nullable', 'numeric'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'], 'deposit_kind' => ['nullable', 'in:amount,percent'], 'deposit_paid' => ['sometimes', 'boolean'],
            'insurance_visible' => ['sometimes', 'boolean'], 'insurance_expenses' => ['nullable', 'numeric', 'min:0'],
            'insurance_company' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'], 'items.*.name' => ['nullable', 'string', 'max:255'], 'items.*.description' => ['nullable', 'string'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'], 'items.*.quantity' => ['nullable', 'numeric', 'min:0'], 'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_one' => ['nullable', 'in:0,15'], 'items.*.tax_two' => ['nullable', 'in:0,15'],
            'custom_fields' => ['nullable', 'array'], 'custom_fields.*.name' => ['nullable', 'string', 'max:255'], 'custom_fields.*.value' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'], 'attachments.*' => ['file', 'max:10240'],
        ]);
    }
}
