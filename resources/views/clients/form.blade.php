@extends('layouts.admin')
@php($editing = request()->routeIs('clients.edit'))
@section('title', ($editing ? 'تعديل العميل' : 'إضافة عميل جديد').' - Titanic ways')
@section('section-name', 'العملاء')
@section('page-name', $editing ? 'تعديل العميل' : 'إضافة عميل جديد')
@push('styles')<link rel="stylesheet" href="{{ asset('clients.css') }}">@endpush
@push('scripts')<script src="{{ asset('clients.js') }}" defer></script>@endpush
@section('page-actions')
<div class="actions client-actions"><div class="action-buttons"><button type="submit" form="client-form" class="btn green">حفظ</button><a class="btn gray" href="{{ $editing ? route('clients.show') : route('clients.index') }}">إلغاء</a></div></div>
@endsection
@section('content')
<form id="client-form" data-mode="{{ $editing ? 'edit' : 'create' }}">
    @include('clients.partials.fields')
    <div class="client-tags">◆ <input aria-label="وسوم العميل" placeholder="وسوم العميل"></div>
    <p class="client-notice" id="client-notice" role="status" hidden></p>
</form>
@endsection
