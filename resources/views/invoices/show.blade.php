@extends('layouts.admin')
@section('title', 'عرض الفاتورة - Titanic Ways')
@section('section-name', 'الفواتير')
@section('page-name', $invoice->number ? 'فاتورة #'.$invoice->number : 'مسودة #'.$invoice->id)
@push('styles')<link rel="stylesheet" href="{{ asset('invoice-pages.css') }}"><link rel="stylesheet" href="{{ asset('fasah-status.css') }}">@endpush
@push('scripts')<script defer src="{{ asset('fasah-status.js') }}"></script>@endpush
@section('page-actions')<div class="actions invoice-view-actions"><div class="invoice-view-title"><div><h1>{{ $invoice->number ? 'الفاتورة #'.$invoice->number : 'مسودة #'.$invoice->id }}</h1></div><div><strong>{{ $invoice->client?->business_name ?? 'بدون عميل' }}</strong></div><span class="invoice-badge {{ $invoice->display_status === 'مدفوعة' ? 'paid' : 'unpaid' }}">{{ $invoice->display_status }}</span></div><a class="btn blue" href="{{ route('invoices.document', $invoice) }}" target="_blank" rel="noopener">▣ معاينة وطباعة</a>@if($invoice->status === 'issued')<a class="btn green" href="{{ route('invoices.pdf', $invoice) }}">↓ تنزيل PDF</a>@endif</div>@endsection
@section('content')
<div class="invoice-view-toolbar">@can('invoices.edit')<a class="btn gray small" href="{{ route('invoices.edit',$invoice) }}">تعديل</a>
@endcan
 @if($invoice->status === 'draft')@can('invoices.delete')<form method="post" action="{{ route('invoices.destroy',$invoice) }}" onsubmit="return confirm('حذف المسودة؟')">@csrf @method('DELETE')<button class="btn gray small" type="submit">حذف المسودة</button></form>
@endcan

@endif
</div>
@if($invoice->status === 'issued')@include('invoices.partials.fasah-status')@endif
<section class="invoice-view-panel invoice-info-panel panel"><h2>تفاصيل الفاتورة</h2><dl><dt>العميل</dt><dd>@if($invoice->client)@can('clients.view')<a href="{{ route('clients.show',$invoice->client) }}">{{ $invoice->client->business_name }}</a>
@else
{{ $invoice->client->business_name }}
@endcan
@else
—
@endif
</dd><dt>التاريخ</dt><dd>{{ $invoice->invoice_date?->format('d/m/Y') ?? '—' }}</dd><dt>مسؤول المبيعات</dt><dd>{{ $invoice->salesStaff?->full_name ?? '—' }}</dd><dt>الحالة</dt><dd>{{ $invoice->display_status }}</dd><dt>الإجمالي</dt><dd>﷼ {{ number_format($invoice->total,2) }}</dd><dt>المدفوع</dt><dd>﷼ {{ number_format($invoice->amount_paid,2) }}</dd><dt>الرصيد</dt><dd>﷼ {{ number_format($invoice->balance,2) }}</dd></dl></section>
<section class="invoice-view-panel invoice-info-panel panel"><h2>البنود</h2><div class="table-scroll"><table><thead><tr><th>البند</th><th>الوصف</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الضريبة 1</th><th>الضريبة 2</th><th>الإجمالي</th></tr></thead><tbody>@forelse($invoice->items as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price,2) }}</td><td>{{ number_format($item->discount,2) }}</td><td>{{ $item->tax_one }}%</td><td>{{ $item->tax_two }}%</td><td>{{ number_format($item->line_total,2) }}</td></tr>
@empty
<tr><td colspan="8">لا توجد بنود.</td></tr>
@endforelse
</tbody></table></div></section>
@if($invoice->customFields->isNotEmpty())<section class="invoice-view-panel invoice-info-panel panel"><h2>حقول مخصصة</h2><dl>@foreach($invoice->customFields as $field)<dt>{{ $field->name }}</dt><dd>{{ $field->value }}</dd>
@endforeach
</dl></section>
@endif

@if($invoice->notes)<section class="invoice-view-panel invoice-info-panel panel"><h2>الملاحظات</h2><p style="white-space:pre-wrap">{{ str_replace('{%unpaid_spelled%}', '﷼ '.number_format($invoice->balance, 2), $invoice->notes) }}</p></section>
@endif

<section class="invoice-view-panel invoice-info-panel panel"><h2>المدفوعات</h2>@forelse($invoice->payments as $payment)<p>{{ $payment->paid_on->format('d/m/Y') }} — {{ $payment->method }} — ﷼ {{ number_format($payment->amount,2) }} {{ $payment->reference }}</p>
@empty
<p>لا توجد دفعات.</p>
@endforelse

@if($invoice->status === 'issued' && $invoice->balance > 0)@can('invoices.pay')<form method="post" action="{{ route('invoices.payments.store',$invoice) }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:end">@csrf<label>المبلغ<input name="amount" type="number" min="0.01" max="{{ $invoice->balance }}" step="0.01" required></label><label>التاريخ<input name="paid_on" type="date" value="{{ now()->toDateString() }}" required></label><label>الطريقة<select name="method"><option>نقدًا</option><option>تحويل بنكي</option><option>أخرى</option></select></label><label>المرجع<input name="reference"></label><button class="btn green" type="submit">تسجيل دفعة</button></form>
@endcan

@endif
</section>
@if($invoice->attachments->isNotEmpty())<section class="invoice-view-panel invoice-info-panel panel"><h2>المرفقات</h2>@foreach($invoice->attachments as $file)<p><a href="{{ route('attachments.download',$file) }}">{{ $file->original_name }}</a></p>
@endforeach
</section>
@endif

@endsection
