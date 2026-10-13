<section class="extras panel">
    <div class="tabs" role="tablist">
        <button class="tab active" data-tab="discount" type="button">الخصم والتسوية</button>
        <button class="tab" data-tab="deposit" type="button">إيداع</button>
        <button class="tab" data-tab="insurance" type="button">بيانات التأمين</button>
        <button class="tab" data-tab="documents" type="button">إرفاق المستندات</button>
    </div>

    <div class="tab-body active" data-panel="discount">
        <label>الخصم <input class="field" id="invoice-discount" name="discount_value" type="number" min="0" step="0.01" value="{{ old('discount_value',$invoice?->discount_value ?? 0) }}"></label>
        @error('discount_value')<small class="field-error">{{ $message }}</small>@enderror
        <label>
            &nbsp;
            <select class="field" id="discount-kind" name="discount_kind">
                <option value="percent" @selected(old('discount_kind',$invoice?->discount_kind) === 'percent')>نسبة مئوية (%)</option>
                <option value="amount" @selected(old('discount_kind',$invoice?->discount_kind) !== 'percent')>مبلغ</option>
            </select>
        </label>
        <label>التسوية <input class="field" id="adjustment" name="adjustment" type="number" step="0.01" value="{{ old('adjustment',$invoice?->adjustment ?? 0) }}"></label>
        @error('adjustment')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="tab-body deposit-panel" data-panel="deposit">
        <div class="deposit-fields">
            <label>الدفعة المقدمة <input class="field" id="deposit-amount" name="deposit_amount" type="number" min="0" step="0.01" value="{{ old('deposit_amount',$invoice?->deposit_amount ?? 0) }}"></label>
            @error('deposit_amount')<small class="field-error">{{ $message }}</small>@enderror
            <label class="deposit-kind-label">&nbsp;<select class="field" id="deposit-kind" name="deposit_kind" aria-label="نوع الدفعة المقدمة"><option value="amount" @selected(old('deposit_kind',$invoice?->deposit_kind) !== 'percent')>المبلغ</option><option value="percent" @selected(old('deposit_kind',$invoice?->deposit_kind) === 'percent')>نسبة مئوية (%)</option></select></label>
        </div>
        <label class="deposit-paid"><input type="checkbox" id="already-paid" name="deposit_paid" value="1" @checked(old('deposit_paid',$invoice?->deposit_paid))><span>مدفوع بالفعل</span></label>
    </div>

    <div class="tab-body insurance-panel" data-panel="insurance">
        <label>بيانات التأمين <select class="field" id="insurance-display" name="insurance_visible"><option value="0" @selected(!old('insurance_visible',$invoice?->insurance_visible))>لا تظهر</option><option value="1" @selected(old('insurance_visible',$invoice?->insurance_visible))>تظهر</option></select></label>
        <label>مصاريف التأمين <input class="field" id="insurance-expenses" name="insurance_expenses" type="number" min="0" step="0.01" value="{{ old('insurance_expenses',$invoice?->insurance_expenses ?? 0) }}"></label>
        @error('insurance_expenses')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="tab-body documents-panel" data-panel="documents">
        <div class="document-tabs" role="tablist" aria-label="المستندات">
            <button type="button" class="document-tab active" data-document-tab="new" aria-selected="true">مستند جديد</button>
            <button type="button" class="document-tab" data-document-tab="existing" aria-selected="false">الملفات التي تم رفعها مسبقًا</button>
        </div>
        <div class="document-content" data-document-panel="new">
            <label class="upload-label" for="invoice-attachments">المرفقات</label>
            <label class="upload-zone" for="invoice-attachments"><span aria-hidden="true">☁</span> أفلت الملف هنا أو <strong>اختر من جهازك</strong></label>
            <input id="invoice-attachments" name="attachments[]" type="file" multiple hidden>
            <ul id="selected-attachments" class="selected-attachments" aria-live="polite"></ul>
        </div>
        <div class="document-content" data-document-panel="existing" hidden>لا توجد ملفات مرفوعة مسبقًا.</div>
    </div>
</section>
