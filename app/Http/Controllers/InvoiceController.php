<?php

namespace App\Http\Controllers;

use App\Actions\RecordInvoicePayment;
use App\Actions\SaveInvoice;
use App\Actions\StoreAttachment;
use App\Http\Requests\InvoiceFormRequest;
use App\Models\Invoice;
use App\Services\FasahPay\FasahLookupValues;
use App\Services\FasahPay\LocalInvoicePayloadBuilder;
use App\Services\FasahPay\SyncLocalInvoice;
use App\Services\LocalReadService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request, LocalReadService $read)
    {
        return view('invoices.index', ['invoices' => $read->invoices($request)]);
    }

    public function create(LocalReadService $read)
    {
        return view('invoices.create', ['invoice' => null, 'clients' => $read->clientOptions(), 'salesPeople' => $read->salesOptions(), 'fasahLookups' => FasahLookupValues::class]);
    }

    public function show(Invoice $invoice, LocalReadService $read)
    {
        return view('invoices.show', ['invoice' => $read->invoiceDetails($invoice)]);
    }

    public function edit(Invoice $invoice, LocalReadService $read, LocalInvoicePayloadBuilder $builder)
    {
        return view($invoice->status === 'draft' ? 'invoices.create' : 'invoices.edit', ['invoice' => $read->invoiceForEditing($invoice), 'clients' => $read->clientOptions(), 'salesPeople' => $read->salesOptions(), 'fasahLookups' => FasahLookupValues::class, 'fasahWarnings' => $builder->warnings($invoice->fasah_meta ?? [], (string) $invoice->fasah_type)]);
    }

    public function store(InvoiceFormRequest $request, SaveInvoice $action, StoreAttachment $files)
    {
        $data = $request->validated();
        $invoice = $action->save($data, null, $request->user());
        foreach ($request->file('attachments', []) as $file) {
            $files->store($invoice, $file, $request->user());
        }

        return redirect()->route('invoices.show', $invoice)->with('status', $invoice->status === 'issued' ? 'تم إصدار الفاتورة محليًا. مزامنة Fasah قيد التنفيذ.' : 'تم حفظ المسودة.');
    }

    public function update(InvoiceFormRequest $request, Invoice $invoice, SaveInvoice $action, StoreAttachment $files)
    {
        $data = $request->validated();
        $invoice = $action->update($invoice, $data, $request->user());
        foreach ($request->file('attachments', []) as $file) {
            $files->store($invoice, $file, $request->user());
        }

        return redirect()->route('invoices.show', $invoice)->with('status', $data['action'] === 'issue' ? 'تم إصدار الفاتورة محليًا. مزامنة Fasah قيد التنفيذ.' : 'تم تحديث الفاتورة.');
    }

    public function fasahStatus(Invoice $invoice, SyncLocalInvoice $sync)
    {
        return response()->json($sync->status($invoice));
    }

    public function retryFasah(Invoice $invoice, SyncLocalInvoice $sync)
    {
        $sync->retryInvoice($invoice);

        return redirect()->route('invoices.show', $invoice)->with('status', 'أُضيفت محاولة مزامنة Fasah إلى قائمة الانتظار.');
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
}
