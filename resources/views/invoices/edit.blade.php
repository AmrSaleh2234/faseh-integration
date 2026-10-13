@extends('layouts.admin')
@section('title', 'تعديل الفاتورة - Titanic Ways')
@section('section-name', 'الفواتير')
@section('page-name', 'فاتورة #'.$invoice->number.' - تعديل')
@push('styles')<link rel="stylesheet" href="{{ asset('invoice-design.css') }}"><link rel="stylesheet" href="{{ asset('invoice-pages.css') }}"><link rel="stylesheet" href="{{ asset('fasah-fields.css') }}">@endpush
@push('scripts')<script defer src="{{ asset('fasah-fields.js') }}"></script>@endpush
@section('page-actions')<div class="actions"><a class="btn gray" href="{{ route('invoices.show',$invoice) }}">إلغاء</a><button class="btn green" type="submit" form="invoice-edit-form">حفظ</button></div>@endsection
@section('content')<div class="issued-edit-warning" role="note">البيانات المالية مقفولة بعد إصدار الفاتورة. يمكن تعديل الملاحظات والحقول المخصصة فقط.</div>
<form id="invoice-edit-form" method="post" enctype="multipart/form-data" action="{{ route('invoices.update',$invoice) }}">@csrf @method('PUT')
<input type="hidden" name="action" value="metadata">
<div class="invoice-grid"><section class="details panel"><div class="detail-row"><label>رقم الفاتورة</label><input class="field" value="{{ $invoice->number }}" disabled></div><div class="detail-row"><label>مسؤول مبيعات</label><select class="field" name="sales_staff_id"><option value="">بدون</option>@foreach($salesPeople as $person)<option value="{{ $person->id }}" @selected($invoice->sales_staff_id == $person->id)>{{ $person->full_name }}</option>
@endforeach
</select></div><div class="detail-row"><label>شروط الدفع بالأيام</label><input class="field" name="payment_terms" type="number" min="0" value="{{ old('payment_terms',$invoice->payment_terms) }}"></div>
@foreach(old('custom_fields',$invoice->customFields->toArray()) as $i=>$field)<div class="detail-row"><input class="field" name="custom_fields[{{ $i }}][name]" value="{{ $field['name'] }}" placeholder="اسم الحقل"><input class="field" name="custom_fields[{{ $i }}][value]" value="{{ $field['value'] }}" placeholder="القيمة"></div>
@endforeach

</section><section class="customer panel"><h2>العميل</h2><p>{{ $invoice->client?->business_name }}</p><p>الإجمالي: ﷼ {{ number_format($invoice->total,2) }}</p><p>الرصيد: ﷼ {{ number_format($invoice->balance,2) }}</p></section></div>
@if($invoice->fasahSync?->status === 'failed')@include('invoices.partials.fasah-fields')@endif
<section class="notes panel"><div class="section-heading">الملاحظات/الشروط</div><textarea class="field" name="notes" rows="8" style="width:100%">{{ old('notes',$invoice->notes) }}</textarea></section><section class="extras panel"><label>شركة التأمين<input class="field" name="insurance_company" value="{{ old('insurance_company',$invoice->insurance_company) }}"></label><label>مرفقات إضافية<input type="file" name="attachments[]" multiple></label></section>
</form>@endsection
