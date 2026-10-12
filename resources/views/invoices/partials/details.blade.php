<section class="details panel" aria-label="بيانات الفاتورة">
    <div class="detail-row">
        <label for="invoice-number">رقم الفاتورة</label>
        <input class="field" id="invoice-number" value="{{ $invoice?->number ?? 'عند الإصدار' }}" readonly>
    </div>

    <div class="detail-row">
        <label for="invoice-date">تاريخ الفاتورة</label>
        <input class="field" id="invoice-date" name="invoice_date" type="date" value="{{ old('invoice_date',$invoice?->invoice_date?->format('Y-m-d') ?? now()->toDateString()) }}">
    </div>

    <div class="detail-row">
        <label for="sales-person">مسئول مبيعات</label>
        <div class="sales-person-control"><select class="field" id="sales-person" name="sales_staff_id"><option value="">اختر موظفًا</option>@foreach($salesPeople as $person)<option value="{{ $person->id }}" @selected(old('sales_staff_id',$invoice?->sales_staff_id) == $person->id)>{{ $person->full_name }} #{{ $person->code }}</option>@endforeach</select>@can('staff.create')<a href="{{ route('staff.create') }}">＋ جديد</a>@endcan</div>
    </div>

    <div class="detail-row">
        <label for="issue-date">تاريخ الإصدار</label>
        <input class="field" id="issue-date" name="issue_date" type="date" value="{{ old('issue_date',$invoice?->issue_date?->format('Y-m-d') ?? now()->toDateString()) }}">
    </div>

    <div class="detail-row">
        <label for="payment-terms">شروط الدفع</label>
        <div class="days">
            <input class="field" id="payment-terms" name="payment_terms" type="number" min="0" value="{{ old('payment_terms',$invoice?->payment_terms ?? 0) }}">
            <span>يوم</span>
        </div>
    </div>

    <div id="custom-fields">
        @foreach (old('custom_fields',$invoice?->customFields?->toArray() ?: collect(['#DEBIT INVOICE','#JOB','JOB DATE','D/O','#BL','VESSEL','VOY','ARRIVAL DATE','FROM','TO'])->map(fn($name)=>['name'=>$name,'value'=>''])->all()) as $field)
            @include('invoices.partials.custom-field-row', ['name' => $field['name'], 'value' => $field['value'] ?? ''])
        @endforeach
    </div>

    <button class="add-field" id="add-field" type="button" aria-label="إضافة حقل مخصص">⊕</button>
</section>

<template id="custom-field-template">
    @include('invoices.partials.custom-field-row', ['name' => '', 'value' => ''])
</template>
