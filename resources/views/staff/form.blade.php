@extends('layouts.admin')
@php($editing = (bool) $staff)
@section('title', ($editing ? 'تعديل موظف' : 'إضافة موظف').' - Titanic Ways')
@section('section-name', 'الموظفين')
@section('page-name', $editing ? 'تعديل موظف' : 'إضافة موظف')
@push('styles')<link rel="stylesheet" href="{{ asset('staff.css') }}">@endpush
@section('page-actions')<div class="actions staff-form-actions"><button class="btn green" type="submit" form="staff-form">حفظ</button><a class="btn gray" href="{{ route('staff.index') }}">إلغاء</a></div>@endsection
@section('content')
<form id="staff-form" method="post" enctype="multipart/form-data" action="{{ $editing ? route('staff.update',$staff) : route('staff.store') }}">@csrf @if($editing)@method('PUT')
@endif

<section class="staff-form-section panel"><h2>معلومات عامة</h2><div class="staff-fields">
@foreach(['first_name'=>'الاسم الأول *','middle_name'=>'الاسم الأوسط','last_name'=>'اللقب *','code'=>'الكود *','email'=>'البريد الإلكتروني'] as $field=>$label)<label>{{ $label }}<input name="{{ $field }}" value="{{ old($field,$staff?->$field) }}" @if(in_array($field,['first_name','last_name','code'])) required
@endif
 @if($field === 'email') type="email"
@endif
></label>
@endforeach

<label>صورة الموظف<input type="file" name="photo" accept="image/*"></label><label class="staff-wide">ملاحظات<textarea name="notes" rows="4">{{ old('notes',$staff?->notes) }}</textarea></label>
<label>نوع الموظف<select name="type"><option @selected(old('type',$staff?->type) === 'موظف')>موظف</option><option @selected(old('type',$staff?->type) === 'مستخدم')>مستخدم</option></select></label>
<label>لغة العرض<select name="language"><option>العربية</option><option @selected(old('language',$staff?->language) === 'English')>English</option></select></label>
<label>الدور الوظيفي<select name="role_id">@foreach($roles as $role)<option value="{{ $role->id }}" @selected(old('role_id',$staff?->role_id) == $role->id)>{{ $role->name }}</option>
@endforeach
</select></label>
<label>الحالة<select name="status"><option @selected(old('status',$staff?->status) === 'نشط')>نشط</option><option @selected(old('status',$staff?->status) === 'غير نشط')>غير نشط</option></select></label>
<label class="staff-check"><input name="login_allowed" value="1" type="checkbox" @checked(old('login_allowed',$staff?->login_allowed))> السماح بالدخول إلى النظام</label>
<label>كلمة المرور {{ $editing ? '(اتركها فارغة للإبقاء عليها)' : '' }}<input name="password" type="password" minlength="12" autocomplete="new-password"></label><label>تأكيد كلمة المرور<input name="password_confirmation" type="password" minlength="12" autocomplete="new-password"></label>
</div></section>
@if($editing)<section class="staff-form-section panel"><h2>معلومات إضافية</h2><div class="staff-fields">@foreach(['birth_date'=>'تاريخ الميلاد','gender'=>'النوع','country'=>'الدولة','citizenship'=>'حالة المواطنة','identity_number'=>'رقم بطاقة الهوية','nationality'=>'الجنسية','residency_expiry'=>'انتهاء الإقامة','mobile'=>'الجوال','phone'=>'الهاتف','personal_email'=>'البريد الشخصي','job_title'=>'المسمى الوظيفي','department'=>'القسم','employment_type'=>'نوع الوظيفة','join_date'=>'تاريخ الالتحاق'] as $field=>$label)<label>{{ $label }}<input name="{{ $field }}" value="{{ old($field,$staff->extra[$field] ?? '') }}" @if(in_array($field,['birth_date','residency_expiry','join_date'])) type="date"
@endif
></label>
@endforeach
</div></section>
@endif

</form>
@endsection
