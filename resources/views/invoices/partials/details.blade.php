<section class="details panel" aria-label="بيانات الفاتورة">
    <div class="detail-row">
        <label for="invoice-number">رقم الفاتورة</label>
        <input class="field" id="invoice-number" value="147">
    </div>

    <div class="detail-row">
        <label for="invoice-date">تاريخ الفاتورة</label>
        <input class="field" id="invoice-date" value="12/10/2026">
    </div>

    <div class="detail-row">
        <label for="sales-person">مسئول مبيعات</label>
        <div class="sales-person-control"><select class="field" id="sales-person"><option>محمد القرني #1</option></select><a href="{{ route('staff.create') }}">＋ جديد</a></div>
    </div>

    <div class="detail-row">
        <label for="issue-date">تاريخ الإصدار</label>
        <input class="field" id="issue-date" value="12/10/2026">
    </div>

    <div class="detail-row">
        <label for="payment-terms">شروط الدفع</label>
        <div class="days">
            <input class="field" id="payment-terms" type="number" min="0">
            <span>يوم</span>
        </div>
    </div>

    <div id="custom-fields">
        @foreach (['#DEBIT INVOICE', '#JOB', 'JOB DATE', 'D/O', '#BL', 'VESSEL', 'VOY', 'ARRIVAL DATE', 'FROM', 'TO'] as $customField)
            @include('invoices.partials.custom-field-row', ['name' => $customField])
        @endforeach
    </div>

    <button class="add-field" id="add-field" type="button" aria-label="إضافة حقل مخصص">⊕</button>
</section>

<template id="custom-field-template">
    @include('invoices.partials.custom-field-row', ['name' => ''])
</template>
