@extends('layouts.admin')
@section('title', 'الموظفين - Titanic ways')
@section('section-name', 'الموظفين')
@section('page-name', 'إدارة الموظفين')
@push('styles')<link rel="stylesheet" href="{{ asset('staff.css') }}">@endpush
@push('scripts')<script type="module" src="{{ asset('staff.js') }}"></script>@endpush

@section('page-actions')
<div class="actions staff-actions"><div class="action-buttons"><a class="btn green" href="{{ route('staff.create') }}">＋ أضف</a><button class="btn gray" type="button" disabled aria-label="تصدير">⇩</button><button class="btn gray" type="button" disabled aria-label="الإعدادات">⚙</button></div><div class="staff-page-count"><span id="staff-count">1 - 1 من 1</span><span>صفحة 1 من 1</span></div></div>
@endsection

@section('content')
<section class="staff-search panel"><div class="staff-search-heading"><strong>بحث وتصفية</strong><button type="button" id="staff-toggle-search">إخفاء ⌃</button></div><form id="staff-search-form"><div class="staff-search-fields"><label>الرقم التعريفي<input id="staff-query" placeholder="ابحث بالاسم، الكود، البريد الإلكتروني..."></label><label>الحالة<select id="staff-status-filter"><option value="">الحالة</option><option>نشط</option><option>غير نشط</option></select></label><label>النوع<select id="staff-type-filter"><option value="">النوع</option><option>مستخدم</option><option>موظف</option></select></label><label>الدور الوظيفي<input id="staff-role-filter" placeholder="الدور الوظيفي"></label></div><div class="staff-search-actions"><button type="submit" class="btn gray small">بحث</button><button type="reset" class="staff-text-button">إعادة تعيين</button></div></form></section>
<section class="staff-table panel"><div class="staff-table-head"><span>الاسم</span><span>الدور الوظيفي</span><span>المسمى الوظيفي</span><span>قسم</span><span>الحالة</span><span>ترتيب ☷</span></div><div id="staff-rows" data-edit-base="{{ url('/v2/owner/staff') }}"></div><p class="staff-empty" id="staff-empty" hidden>لا يوجد موظفون مطابقون للبحث.</p></section>
@endsection
