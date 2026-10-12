@extends('layouts.admin')
@section('title', 'الفواتير - Titanic ways')
@section('section-name', 'الفواتير')
@section('page-name', 'القائمة')
@push('styles')<link rel="stylesheet" href="{{ asset('invoice-pages.css') }}">@endpush
@push('scripts')<script type="module" src="{{ asset('invoice-pages.js') }}"></script>@endpush

@section('page-actions')
<div class="actions invoice-list-actions">
    <div class="action-buttons"><a class="btn green" href="{{ route('invoices.create') }}">＋ إضافة</a><button class="btn gray" type="button" aria-label="خيارات القائمة" disabled>•••</button></div>
    <div class="invoice-list-pager"><label><input type="checkbox" id="invoice-select-all" aria-label="تحديد الكل"></label><span id="invoice-count">1 - 4 من 4</span><button type="button" disabled aria-label="الصفحة السابقة">‹</button><span>صفحة 1 من 1</span><button type="button" disabled aria-label="الصفحة التالية">›</button></div>
</div>
@endsection

@section('content')
<section class="invoice-filter panel">
    <div class="invoice-filter-head">بحث</div>
    <form id="invoice-search">
        <div class="invoice-filter-grid">
            <label>العميل<input id="invoice-client-search" placeholder="أي عميل"></label>
            <label>رقم الفاتورة<input id="invoice-number-search" placeholder="رقم الفاتورة" inputmode="numeric"></label>
            <label>الحالة<select id="invoice-status-search"><option value="">أي حالة</option><option>غير مدفوعة</option><option>مدفوعة</option><option>مسودة</option></select></label>
        </div>
        <div class="invoice-filter-buttons"><button class="btn gray small" type="submit">بحث</button><button class="btn gray small" type="reset">إلغاء الفلتر</button><button class="btn gray small" id="invoice-advanced-toggle" type="button" aria-expanded="false">☷ بحث متقدم</button></div>
        <div class="invoice-advanced" id="invoice-advanced" hidden><label>من تاريخ <input type="date" id="invoice-date-from"></label><label>إلى تاريخ <input type="date" id="invoice-date-to"></label></div>
    </form>
</section>
<section class="invoice-results panel">
    <div class="invoice-results-head"><strong>النتائج</strong><div class="invoice-status-tabs" role="tablist"><button class="active" type="button" data-status="">الكل</button><button type="button" data-status="متأخر">متأخر</button><button type="button" data-status="مستحقة الدفع">مستحقة الدفع</button><button type="button" data-status="غير مدفوعة">غير مدفوعة</button><button type="button" data-status="مسودة">مسودة</button><button type="button" data-status="مدفوعة">مدفوعة</button></div></div>
    @php($invoices = [
        ['146', '11/10/2026', 'مؤسسة بلقيس أكرم محمد أكرم عمر دين للتجارة', '57,492.50', 'غير مدفوعة', 'تم الإرسال'],
        ['145', '11/10/2026', 'عميل تجريبي', '18,078.00', 'مدفوعة', 'أضيفت عملية دفع'],
        ['144', '08/10/2026', 'شركة شحن تجريبية', '30,570.00', 'مدفوعة', 'أضيفت عملية دفع'],
        ['143', '06/10/2026', 'مؤسسة تجارية تجريبية', '34,645.00', 'مدفوعة', 'أضيفت عملية دفع'],
    ])
    <div id="invoice-rows">
        @foreach($invoices as [$number, $date, $client, $amount, $status, $event])
        <article class="invoice-result-row" data-number="{{ $number }}" data-client="{{ mb_strtolower($client) }}" data-status="{{ $status }}" data-date="{{ implode('-', array_reverse(explode('/', $date))) }}">
            <div class="invoice-result-identity"><div class="invoice-result-meta">{{ $number }} - {{ $date }}</div>@if($loop->first)<a href="{{ route('invoices.show') }}">{{ $client }}</a>@else<strong>{{ $client }}</strong>@endif<small>بواسطة: المستخدم الحالي</small></div>
            <div class="invoice-result-event"><b>{{ $event }}</b><small>{{ $date }}</small></div>
            <div class="invoice-result-amount"><strong>﷼ {{ $amount }}</strong><span class="invoice-badge {{ $status === 'مدفوعة' ? 'paid' : 'unpaid' }}">{{ $status }}</span></div>
            <div class="invoice-result-menu">@if($loop->first)<button type="button" class="invoice-row-menu" aria-label="إجراءات الفاتورة 146" aria-expanded="false">•••</button><div class="invoice-row-options" hidden><a href="{{ route('invoices.show') }}">عرض</a><a href="{{ route('invoices.edit') }}">تعديل</a></div>@endif</div>
            <input class="invoice-row-check" type="checkbox" aria-label="تحديد الفاتورة {{ $number }}">
        </article>
        @endforeach
    </div>
    <p class="invoice-empty" id="invoice-empty" hidden>لا توجد فواتير تطابق البحث.</p>
</section>
@endsection
