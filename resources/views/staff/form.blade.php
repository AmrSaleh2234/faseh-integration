@extends('layouts.admin')
@php($editing = request()->routeIs('staff.edit'))
@section('title', ($editing ? 'تعديل موظف' : 'إضافة موظف').' - Titanic ways')
@section('section-name', 'الموظفين')
@section('page-name', $editing ? 'تعديل موظف' : 'إضافة موظف')
@push('styles')<link rel="stylesheet" href="{{ asset('staff.css') }}">@endpush
@push('scripts')<script type="module" src="{{ asset('staff.js') }}"></script>@endpush

@section('page-actions')
<div class="actions staff-form-actions"><div class="action-buttons"><button class="btn green" type="submit" form="staff-form">حفظ</button><a class="btn gray" href="{{ route('staff.index') }}">إلغاء</a></div></div>
@endsection

@section('content')
<form id="staff-form" data-staff-id="{{ $editing ? request()->route('staff') : '' }}" data-list-url="{{ route('staff.index') }}">
    @include('staff.partials.form-fields')
    <p class="staff-notice" id="staff-notice" role="status" hidden></p>
</form>
@endsection
