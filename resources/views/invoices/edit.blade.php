@extends('layouts.admin')
@section('title', 'تعديل الفاتورة - Titanic ways')
@section('section-name', 'الفواتير')
@section('page-name', 'فاتورة #146 - تعديل')
@push('styles')<link rel="stylesheet" href="{{ asset('invoice-design.css') }}"><link rel="stylesheet" href="{{ asset('invoice-pages.css') }}">@endpush
@push('scripts')<script type="module" src="{{ asset('invoice-pages.js') }}"></script>@endpush

@section('page-actions')
<div class="actions"><div class="action-buttons"><button class="btn blue" type="button" id="edit-preview">◉ معاينة</button><a class="btn gray" href="{{ route('invoices.show') }}">إلغاء</a></div><button class="btn green" type="submit" form="issued-invoice-form">حفظ دون طباعة</button></div>
@endsection

@section('content')
<div class="issued-edit-warning" role="note">لا يمكن تعديل البيانات المالية بعد إصدار الفاتورة الإلكترونية. يمكنك تعديل الحقول المخصصة والملاحظات والحقول غير المالية.</div>
<form id="issued-invoice-form">
    <section class="template panel"><label>قالب الفاتورة<select disabled><option>التصميم الافتراضي للفاتورة</option></select></label></section>
    <div class="invoice-grid">
        <section class="details panel" aria-label="بيانات الفاتورة">
            <div class="detail-row"><label for="issued-number">رقم الفاتورة</label><input class="field" id="issued-number" value="146" disabled></div>
            <div class="detail-row"><label for="issued-date">تاريخ الفاتورة</label><input class="field" id="issued-date" value="11/10/2026" disabled></div>
            <div class="detail-row"><label for="issued-sales">مسؤول مبيعات</label><div class="sales-person-control"><select class="field" id="issued-sales"><option>محمد القرني #1</option></select><a href="{{ route('staff.create') }}">＋ جديد</a></div></div>
            <div class="detail-row"><label for="issued-issue-date">تاريخ الإصدار</label><input class="field" id="issued-issue-date" value="11/10/2026" disabled></div>
            <div class="detail-row"><label for="issued-terms">شروط الدفع</label><div class="days"><input class="field" id="issued-terms" type="number" min="0" value="0"><span>يوم</span></div></div>
            <div id="issued-custom-fields">
                @foreach(['DEBIT INVOICE#'=>'JED/10/2026/0049','JOB#'=>'TWC-0049/10/26','JOB DATE'=>'TWC-0049/10/26','D/O'=>'','BL#'=>'FALKHIJED1003','VESSEL'=>'MEDIKON SIA','VOY'=>'26001E','ARRIVAL DATE'=>'13/10/2026','FROM'=>'KARACHI','TO'=>'JEDDAH'] as $name=>$value)
                <div class="detail-row issued-custom-row"><label>{{ $name }}</label><input class="field" data-field-name="{{ $name }}" value="{{ $value }}"></div>
                @endforeach
            </div>
            <button class="add-field" id="issued-add-field" type="button" aria-label="إضافة حقل مخصص">⊕</button>
        </section>
        <section class="customer panel issued-customer" aria-label="بيانات العميل"><div class="customer-row"><label>الطريقة:</label><input class="field" value="طباعة" disabled></div><div class="customer-row"><label>العميل</label><input class="field" value="مؤسسة بلقيس أكرم محمد أكرم عمر دين للتجارة" disabled></div><div class="issued-customer-balance"><strong>﷼ 57,492.50</strong><small>الرصيد المفتوح</small><p>⌖ الخبر</p></div></section>
    </div>
    <section class="items panel issued-locked-items" aria-label="بنود الفاتورة"><div class="table-scroll"><table><thead><tr><th>Item</th><th>Description</th><th>Unit Price</th><th>Quantity</th><th>الخصم</th><th>الضريبة 1</th><th>الضريبة 2</th><th>Subtotal</th></tr></thead><tbody><tr><td>Ocean Fright</td><td>Ocean Fright</td><td>57,492.50</td><td>1</td><td>0</td><td>صفرية</td><td>بدون ضريبة</td><td>﷼ 57,492.50</td></tr></tbody></table></div><div class="issued-locked-total"><span>الإجمالي</span><strong>﷼ 57,492.50</strong></div></section>
    <section class="extras panel"><div class="tabs"><button class="tab active" type="button" data-edit-tab="discount">الخصم والتسوية</button><button class="tab" type="button" data-edit-tab="deposit">إيداع</button><button class="tab" type="button" data-edit-tab="insurance">بيانات التأمين</button><button class="tab" type="button" data-edit-tab="documents">إرفاق المستندات</button></div><div class="tab-body active" data-edit-panel="discount"><label>الخصم<input class="field" value="0" disabled></label><label>التسوية<input class="field" value="0.00" disabled></label></div><div class="tab-body" data-edit-panel="deposit"><p>لا يمكن تعديل الإيداع بعد إصدار الفاتورة.</p></div><div class="tab-body" data-edit-panel="insurance"><label>شركة التأمين<input class="field" id="issued-insurance" placeholder="شركة التأمين"></label></div><div class="tab-body" data-edit-panel="documents"><label>إرفاق المستندات<input type="file" id="issued-files" multiple></label></div></section>
    @include('invoices.partials.notes')
    <p class="invoice-local-notice" id="invoice-edit-notice" role="status" hidden></p>
</form>
<template id="issued-custom-template"><div class="detail-row issued-custom-row"><input class="field" aria-label="اسم الحقل" placeholder="اسم الحقل"><input class="field" aria-label="قيمة الحقل" placeholder="قيمة الحقل"></div></template>
@endsection

@push('dialogs')
<div class="issued-preview-dialog" id="issued-preview-dialog" hidden role="dialog" aria-modal="true" aria-label="معاينة الفاتورة"><div class="issued-preview-shell"><button type="button" id="issued-preview-close" aria-label="إغلاق المعاينة">×</button>@include('invoices.partials.issued-document')</div></div>
@endpush
