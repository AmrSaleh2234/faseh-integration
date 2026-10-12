@extends('layouts.admin')
@section('title', 'الفواتير - Titanic Ways')
@section('section-name', 'الفواتير')
@section('page-name', 'القائمة')
@push('styles')<link rel="stylesheet" href="{{ asset('invoice-pages.css') }}">@endpush
@section('page-actions')<div class="actions invoice-list-actions">@can('invoices.create')<a class="btn green" href="{{ route('invoices.create') }}">＋ إضافة</a>
@endcan
 <span>{{ $invoices->total() }} فاتورة</span></div>@endsection
@section('content')
<section class="invoice-filter panel"><div class="invoice-filter-head">بحث</div><form method="get" action="{{ route('invoices.index') }}" id="invoice-search"><div class="invoice-filter-grid"><label>العميل<input name="client" value="{{ request('client') }}" placeholder="اسم العميل"></label><label>رقم الفاتورة<input name="number" value="{{ request('number') }}" placeholder="رقم الفاتورة"></label><label>الحالة<select name="status"><option value="">أي حالة</option>@foreach(['غير مدفوعة','مدفوعة','مسودة'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>
@endforeach
</select></label><label>من تاريخ<input type="date" name="from" value="{{ request('from') }}"></label><label>إلى تاريخ<input type="date" name="to" value="{{ request('to') }}"></label><label>بحث عام<input name="q" value="{{ request('q') }}"></label></div><div class="invoice-filter-buttons"><button class="btn gray small" type="submit">بحث</button><a class="btn gray small" href="{{ route('invoices.index') }}">مسح الفلاتر</a></div></form></section>
<section class="invoice-results panel"><div class="invoice-results-head"><strong>النتائج</strong><div class="invoice-status-tabs">@foreach([''=>'الكل','غير مدفوعة'=>'غير مدفوعة','مدفوعة'=>'مدفوعة','مسودة'=>'مسودة'] as $value=>$label)<a class="{{ request('status','') === $value ? 'active' : '' }}" href="{{ route('invoices.index',['status'=>$value]) }}">{{ $label }}</a>
@endforeach
</div></div><div id="invoice-rows">
@forelse($invoices as $invoice)<article class="invoice-result-row"><div class="invoice-result-identity"><div class="invoice-result-meta">{{ $invoice->number ? '#'.$invoice->number : 'مسودة #'.$invoice->id }} — {{ $invoice->invoice_date?->format('d/m/Y') }}</div><a href="{{ route('invoices.show',$invoice) }}">{{ $invoice->client?->business_name ?? 'بدون عميل' }}</a></div><div class="invoice-result-event"><b>{{ $invoice->status === 'draft' ? 'مسودة' : 'صادرة' }}</b><small>{{ $invoice->created_at->format('d/m/Y') }}</small></div><div class="invoice-result-amount"><strong>﷼ {{ number_format($invoice->total,2) }}</strong><span class="invoice-badge {{ $invoice->display_status === 'مدفوعة' ? 'paid' : 'unpaid' }}">{{ $invoice->display_status }}</span></div><div class="invoice-result-menu"><a href="{{ route('invoices.show',$invoice) }}">عرض</a> @can('invoices.edit')<a href="{{ route('invoices.edit',$invoice) }}">تعديل</a>
@endcan
</div></article>
@empty
<p class="invoice-empty">لا توجد فواتير بعد.</p>
@endforelse

</div></section><div style="margin-top:16px">{{ $invoices->links() }}</div>
@endsection
