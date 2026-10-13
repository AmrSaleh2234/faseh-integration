@extends('layouts.admin')
@php($editing = (bool) $client)
@section('title', ($editing ? 'تعديل العميل' : 'إضافة عميل').' - Titanic Ways')
@section('section-name', 'العملاء')
@section('page-name', $editing ? 'تعديل العميل' : 'إضافة عميل')
@push('styles')<link rel="stylesheet" href="{{ asset('clients.css') }}?v=20261013">@endpush
@section('page-actions')<div class="actions"><button class="btn green" type="submit" form="client-form">حفظ</button><a class="btn gray" href="{{ $editing ? route('clients.show', $client) : route('clients.index') }}">إلغاء</a></div>@endsection
@section('content')
<section class="app-feedback {{ empty($fasahIssues) ? '' : 'error' }}" id="client-fasah-readiness" role="status" aria-live="polite">
    <strong>{{ empty($fasahIssues) ? 'جاهز لفاتورة Fasah عامة' : 'يمكن حفظ العميل الآن؛ يلزم استكمال البيانات التالية قبل إصدار فاتورة Fasah عامة:' }}</strong>
    <ul>@foreach($fasahIssues as $issue)<li>{{ $issue }}</li>@endforeach</ul>
</section>
<form id="client-form" method="post" enctype="multipart/form-data" action="{{ $editing ? route('clients.update', $client) : route('clients.store') }}">@csrf @if($editing)@method('PUT')
@endif

<div class="client-columns"><section class="client-card"><h2>بيانات العميل</h2><div class="client-card-body">
<div class="client-field"><label>نوع العميل</label><div class="radio-row"><label><input type="radio" name="type" value="individual" @checked(old('type',$client?->type ?? 'individual') === 'individual')> فردي</label><label><input type="radio" name="type" value="business" @checked(old('type',$client?->type) === 'business')> تجاري</label></div></div>
@foreach(['business_name'=>'الاسم التجاري *','first_name'=>'الاسم الأول','last_name'=>'الاسم الأخير','phone'=>'هاتف','mobile'=>'جوال','building'=>'رقم المبنى','street'=>'اسم الشارع','secondary_number'=>'الرقم الإضافي','district'=>'الحي','city'=>'المدينة','postal'=>'الرمز البريدي','short_address'=>'العنوان المختصر','tax_number'=>'الرقم الضريبي','registration'=>'السجل التجاري'] as $field=>$label)
<div class="client-field"><label>{{ $label }}<input name="{{ $field }}" value="{{ old($field, in_array($field,['building','street','secondary_number','district','postal','short_address']) ? ($client?->address[$field] ?? '') : ($client?->$field ?? '')) }}" @if($field === 'business_name') required
@endif
></label>@error($field)<small class="field-error">{{ $message }}</small>@enderror</div>

@endforeach

<div class="client-field"><label>الدولة<select name="country">@foreach(['المملكة العربية السعودية','مصر','الإمارات العربية المتحدة'] as $country)<option @selected(old('country',$client?->country) === $country)>{{ $country }}</option>
@endforeach
</select></label></div><label class="client-check"><input type="checkbox" name="government" value="1" @checked(old('government',$client?->government))> جهة حكومية</label>
<div class="client-field"><label>عنوان ثانوي<textarea name="second_address">{{ old('second_address',$client?->address['second_address'] ?? '') }}</textarea></label></div>
<h3>جهات الاتصال</h3><div id="contacts">@foreach(old('contacts',$client?->contacts?->toArray() ?? []) as $i=>$contact)<div class="contact-row"><input name="contacts[{{ $i }}][name]" value="{{ $contact['name'] ?? '' }}" placeholder="الاسم"><input name="contacts[{{ $i }}][email]" type="email" value="{{ $contact['email'] ?? '' }}" placeholder="البريد الإلكتروني">@error('contacts.'.$i.'.email')<small class="field-error">{{ $message }}</small>@enderror</div>
@endforeach
</div><button type="button" class="client-link" id="add-contact">＋ أضف جهة اتصال</button>
</div></section><section class="client-card"><h2>بيانات الحساب</h2><div class="client-card-body">
@foreach(['code'=>'الكود *','email'=>'البريد الإلكتروني','classification'=>'تصنيف العميل'] as $field=>$label)<div class="client-field"><label>{{ $label }}<input name="{{ $field }}" value="{{ old($field,$client?->$field) }}" @if($field === 'code') required
@endif
 @if($field === 'email') type="text" inputmode="email"
@endif
></label>@error($field)<small class="field-error">{{ $message }}</small>@enderror</div>
@endforeach

<div class="client-field"><label>طريقة إرسال الفواتير<select name="billing"><option>طباعة</option><option @selected(old('billing',$client?->billing) === 'البريد الإلكتروني')>البريد الإلكتروني</option></select></label></div><div class="client-field"><label>العملة<input value="SAR" disabled></label></div><div class="client-field"><label>ملاحظات<textarea name="notes" rows="5">{{ old('notes',$client?->notes) }}</textarea></label></div><div class="client-field"><label>مرفقات<input name="attachments[]" type="file" multiple></label></div><div class="client-field"><label>لغة العرض<select name="language"><option>العربية</option><option @selected(old('language',$client?->language) === 'English')>English</option></select></label></div>
@if($editing)@foreach($client->attachments as $file)<a href="{{ route('attachments.download',$file) }}">{{ $file->original_name }}</a>
@endforeach

@endif

</div></section></div></form>
<template id="contact-template"><div class="contact-row"><input placeholder="الاسم"><input type="email" placeholder="البريد الإلكتروني"><button type="button" class="remove-contact">حذف</button></div></template>
<script>document.getElementById('add-contact').addEventListener('click',()=>{const box=document.getElementById('contacts'),i=box.children.length,row=document.getElementById('contact-template').content.firstElementChild.cloneNode(true);row.children[0].name=`contacts[${i}][name]`;row.children[1].name=`contacts[${i}][email]`;box.append(row)});document.getElementById('contacts').addEventListener('click',e=>{if(e.target.classList.contains('remove-contact'))e.target.parentElement.remove()})</script>
<script defer src="{{ asset('client-readiness.js') }}"></script>
@endsection
