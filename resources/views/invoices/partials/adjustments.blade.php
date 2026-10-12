<section class="extras panel">
    <div class="tabs" role="tablist">
        <button class="tab active" data-tab="discount" type="button">الخصم والتسوية</button>
        <button class="tab" data-tab="deposit" type="button">إيداع</button>
        <button class="tab" data-tab="insurance" type="button">بيانات التأمين</button>
        <button class="tab" data-tab="documents" type="button">إرفاق المستندات</button>
    </div>

    <div class="tab-body active" data-panel="discount">
        <label>الخصم <input class="field" id="invoice-discount" type="number" min="0" value="0"></label>
        <label>
            &nbsp;
            <select class="field" id="discount-kind">
                <option value="percent">نسبة مئوية (%)</option>
                <option value="amount">مبلغ</option>
            </select>
        </label>
        <label>التسوية <input class="field" id="adjustment" type="number" value="0.00"></label>
    </div>

    <div class="tab-body" data-panel="deposit">
        <label>الإيداع <input class="field" type="number" min="0" placeholder="0.00"></label>
    </div>

    <div class="tab-body" data-panel="insurance">
        <label>شركة التأمين <input class="field" placeholder="شركة التأمين"></label>
    </div>

    <div class="tab-body" data-panel="documents">
        <label>إرفاق المستندات <input type="file" multiple></label>
    </div>
</section>
