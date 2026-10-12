@extends('layouts.admin')

@section('title', 'فاتورة جديدة - Titanic ways')
@section('section-name', 'الفواتير')
@section('page-name', 'إضافة')

@push('styles')
    <link rel="stylesheet" href="{{ asset('invoice-design.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('invoice-design.js') }}"></script>
@endpush

@section('page-actions')
    <div class="actions">
        <div class="action-buttons">
            <button class="btn blue" id="preview" type="button">◉ &nbsp; معاينة</button>
            <button class="btn gray" id="save-draft" type="submit" form="invoice-form" name="action" value="draft">حفظ كمسودة</button>
        </div>

        <button class="btn green" id="save-invoice" type="submit" form="invoice-form" name="action" value="issue">حفظ دون طباعة</button>
    </div>
@endsection

@section('content')
<form id="invoice-form" method="post" enctype="multipart/form-data" action="{{ $invoice ? route('invoices.update',$invoice) : route('invoices.store') }}">
    @csrf @if($invoice)@method('PUT')@endif
    @include('invoices.partials.template-selector')

    <div class="invoice-grid">
        @include('invoices.partials.details')
        @include('invoices.partials.customer')
    </div>

    @include('invoices.partials.items')

    <div class="tag-line">
        ◆ <input aria-label="وسوم الصنف" placeholder="وسوم الصنف">
    </div>

    @include('invoices.partials.adjustments')
    @include('invoices.partials.notes')
    <input type="hidden" name="notes" id="notes-value">
</form>
@endsection

@push('dialogs')
    @include('invoices.partials.preview-dialog')
@endpush
