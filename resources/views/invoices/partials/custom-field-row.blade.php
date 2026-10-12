<div class="detail-row custom-row">
    <input class="field custom-name" value="{{ $name }}" placeholder="اسم الحقل" aria-label="اسم الحقل">

    <div class="custom-value">
        <input class="field" aria-label="قيمة {{ $name ?: 'الحقل' }}">
        <button class="remove-custom" type="button" aria-label="حذف الحقل">×</button>
    </div>
</div>
