@extends('layouts.admin')
@section('title', 'فاتورة رقم 146 - Titanic ways')
@section('section-name', 'الفواتير')
@section('page-name', 'فاتورة #146')
@push('styles')<link rel="stylesheet" href="{{ asset('invoice-pages.css') }}">@endpush
@push('scripts')<script type="module" src="{{ asset('invoice-pages.js') }}"></script>@endpush

@section('page-actions')
<div class="actions invoice-view-actions"><div class="invoice-view-title"><div><h1>الفاتورة #146</h1><small>Journal #547</small></div><div><strong>مؤسسة بلقيس أكرم محمد أكرم عمر دين للتجارة</strong><a href="{{ route('clients.show') }}">العميل: 044</a></div><span class="invoice-badge unpaid">غير مدفوعة</span></div><button class="btn blue" type="button" id="invoice-print">▣ طباعة</button></div>
@endsection

@section('content')
<div class="invoice-view-toolbar"><a class="btn gray small" href="{{ route('invoices.edit') }}">تعديل</a><button type="button" class="btn gray small" data-invoice-action="إضافة عملية دفع">إضافة عملية دفع</button><button type="button" class="btn gray small" data-invoice-action="نسخ الفاتورة">نسخ</button><button type="button" class="btn gray small" data-invoice-action="إرسال الفاتورة">أرسل عبر</button></div>
<div class="invoice-view-tabs" role="tablist"><button class="active" type="button" role="tab" aria-selected="true" data-invoice-tab="invoice">فاتورة</button><button type="button" role="tab" aria-selected="false" data-invoice-tab="details">التفاصيل</button><button type="button" role="tab" aria-selected="false" data-invoice-tab="activity">سجل النشاطات</button></div>
<section class="invoice-view-panel" id="invoice-tab-invoice" role="tabpanel"><div class="issued-document-wrap">@include('invoices.partials.issued-document')</div></section>
<section class="invoice-view-panel invoice-info-panel" id="invoice-tab-details" role="tabpanel" hidden><h2>تفاصيل الفاتورة</h2><dl><dt>رقم الفاتورة</dt><dd>146</dd><dt>تاريخ الفاتورة</dt><dd>11/10/2026</dd><dt>العميل</dt><dd>مؤسسة بلقيس أكرم محمد أكرم عمر دين للتجارة</dd><dt>حالة الدفع</dt><dd>غير مدفوعة</dd><dt>الإجمالي</dt><dd>﷼ 57,492.50</dd><dt>الرصيد المستحق</dt><dd>﷼ 57,492.50</dd></dl></section>
<section class="invoice-view-panel invoice-info-panel" id="invoice-tab-activity" role="tabpanel" hidden><h2>سجل النشاطات</h2><p>11/10/2026 — تم إرسال الفاتورة بواسطة المستخدم الحالي.</p></section>
<p class="invoice-local-notice" id="invoice-action-notice" role="status" hidden></p>
@endsection
