@extends('layouts.admin')
@section('title', 'العملاء - Titanic ways')
@section('section-name', 'العملاء')
@section('page-name', 'إدارة العملاء')
@push('styles')<link rel="stylesheet" href="{{ asset('clients.css') }}">@endpush
@push('scripts')<script src="{{ asset('clients.js') }}" defer></script>@endpush
@section('page-actions')
<div class="actions client-actions"><div class="action-buttons"><button class="btn gray client-icon-button" type="button" title="استيراد">☁</button><button class="btn gray client-icon-button" type="button" title="الإعدادات">⚙</button><a href="{{ route('clients.create') }}" class="btn green">＋ أضف العميل</a></div><div class="client-pagination"><label><input type="checkbox" id="select-all" aria-label="تحديد الكل"></label><span id="result-count">1 - 6 من 6</span><button type="button" id="prev-page" aria-label="الصفحة السابقة">‹</button><span id="page-number">صفحة 1 من 1</span><button type="button" id="next-page" aria-label="الصفحة التالية">›</button></div></div>
@endsection
@section('content')
<section class="client-filter panel"><div class="client-filter-heading"><strong>بحث وتصفية</strong><button type="button" id="toggle-filters">إخفاء ⌃</button></div><form id="client-search"><div class="client-filter-grid"><label><span>العميل</span><select id="filter-client"><option value="">العميل</option><option value="business">تجاري</option><option value="individual">فردي</option></select></label><label><span>الاسم</span><input id="filter-name" placeholder="الاسم"></label><label><span>الحالة</span><select id="filter-status"><option value="">الحالة</option><option>نشط</option><option>غير نشط</option></select></label><label><span>تصنيفات العملاء</span><select id="filter-class"><option value="">اختر تصنيفات العملاء</option><option>شركة</option><option>عميل نشط</option></select></label></div><div class="client-filter-footer"><button class="btn gray small" type="submit">بحث</button><button class="client-link" type="reset">إعادة تعيين</button></div></form></section>
<section class="client-list panel"><div class="client-list-header"><span>العميل</span><span>بيانات الاتصال</span><span>المسؤول</span><span>ترتيب ☷</span></div>
@php($clients = [
    ['مؤسسة بلقيس أكرم محمد أكرم عمر دين للتجارة', '044', 'الخبر', 'business'],
    ['مؤسسة الشراع التجارية', '043', 'الرياض', 'business'],
    ['محمد سرناب', '042', '', 'individual'],
    ['مصنع موديرن هاوس', '041', '', 'business'],
    ['وكالة ANC', '036', '', 'business'],
    ['GLOBAL LOGISTICS', '033', '', 'business'],
])
<div id="client-rows">
@foreach($clients as [$name, $code, $city, $type])
<div class="client-list-row" data-name="{{ mb_strtolower($name) }}" data-type="{{ $type }}" data-status="نشط"><div class="client-identity"><input type="checkbox" class="row-check" aria-label="تحديد {{ $name }}"><span class="client-avatar color-{{ $loop->index % 4 }}">{{ mb_substr($name, 0, 1) }}</span><div>@if($loop->first)<a href="{{ route('clients.show') }}">{{ $name }} ↗</a>@else<span>{{ $name }}</span>@endif<small>#{{ $code }}</small></div></div><div class="client-contact-cell">@if($loop->first)<span>☎ +966 56 488 2976</span><span>✉ Ops@myfreight.biz</span>@endif @if($city)<span>⌖ {{ $city }}</span>@endif</div><div class="client-owner">♙ محمد القرني</div><div>@if($loop->first)<button type="button" class="row-menu" aria-label="إجراءات {{ $name }}">•••</button><div class="row-menu-panel" hidden><a href="{{ route('clients.show') }}">عرض</a><a href="{{ route('clients.edit') }}">تعديل</a></div>@endif</div></div>
@endforeach
</div><p class="client-empty" id="client-empty" hidden>لا توجد نتائج مطابقة.</p></section>
@endsection
