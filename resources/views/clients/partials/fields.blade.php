@php($editing = request()->routeIs('clients.edit'))
<div class="client-columns">
    <section class="client-card">
        <h2>بيانات العميل</h2>
        <div class="client-card-body">
            <div class="client-field"><label>نوع العميل</label><div class="radio-row"><label><input type="radio" name="type" value="individual" @checked(!$editing)> فردي</label><label><input type="radio" name="type" value="business" @checked($editing)> تجاري</label></div></div>
            <div class="client-field"><label for="business-name">الاسم التجاري <em>*</em></label><input id="business-name" name="business_name" required value="{{ $editing ? 'مؤسسة بلقيس أكرم محمد أكرم عمر دين للتجارة' : '' }}"></div>
            <div class="client-pair"><div class="client-field"><label for="first-name">الاسم الأول</label><input id="first-name" name="first_name"></div><div class="client-field"><label for="last-name">الاسم الأخير</label><input id="last-name" name="last_name"></div></div>
            <div class="client-pair"><div class="client-field"><label for="phone">هاتف</label><input id="phone" name="phone" inputmode="tel"></div><div class="client-field"><label for="mobile">جوال</label><input id="mobile" name="mobile" inputmode="tel" value="{{ $editing ? '+966 56 488 2976' : '' }}"></div></div>
            <h3>العنوان</h3>
            <div class="client-pair"><div class="client-field"><label for="building">رقم المبنى</label><input id="building" name="building"></div><div class="client-field"><label for="street">اسم الشارع</label><input id="street" name="street"></div></div>
            <div class="client-pair"><div class="client-field"><label for="secondary-number">الرقم الإضافي</label><input id="secondary-number" name="secondary_number"></div><div class="client-field"><label for="district">الحي</label><input id="district" name="district"></div></div>
            <div class="client-pair"><div class="client-field"><label for="city">المدينة</label><input id="city" name="city" value="{{ $editing ? 'الخبر' : '' }}"></div><div class="client-field"><label for="postal">الرمز البريدي</label><input id="postal" name="postal"></div></div>
            <div class="client-field"><label for="short-address">العنوان المختصر</label><input id="short-address" name="short_address"></div>
            <div class="client-field"><label for="country">الدولة</label><select id="country" name="country"><option>المملكة العربية السعودية</option><option>مصر</option><option>الإمارات العربية المتحدة</option></select></div>
            <label class="client-check"><input type="checkbox" name="government"> جهة حكومية</label>
            <div class="client-pair"><div class="client-field"><label for="tax-number">الرقم الضريبي</label><input id="tax-number" name="tax_number"></div><div class="client-field"><label for="registration">السجل التجاري / الرقم الموحد</label><input id="registration" name="registration"></div></div>
            <div class="map-placeholder">⌖<br>حدد الموقع على الخريطة</div>
            <label class="client-check"><input type="checkbox" id="second-address-toggle"> عنوان ثانوي</label>
            <div id="second-address" hidden class="client-field"><label for="second-address-text">العنوان الثانوي</label><textarea id="second-address-text" name="second_address"></textarea></div>
            <div class="client-section-heading"><h3>جهات الاتصال</h3><button class="client-link" type="button" id="add-contact">+ أضف جهة اتصال</button></div>
            <div id="contacts"></div>
        </div>
    </section>
    <section class="client-card">
        <h2>بيانات الحساب</h2>
        <div class="client-card-body">
            <div class="client-field"><label for="client-code">الكود <em>*</em></label><input id="client-code" name="code" required value="{{ $editing ? '045' : '045' }}"></div>
            <div class="client-field"><label for="billing">طريقة إرسال الفواتير</label><select id="billing" name="billing"><option>طباعة</option><option>البريد الإلكتروني</option></select></div>
            <div class="client-field"><label for="client-currency">العملة</label><select id="client-currency" name="currency"><option>ريال سعودي (SAR)</option><option>دولار أمريكي (USD)</option></select></div>
            <div class="client-field"><label for="client-email">البريد الإلكتروني</label><input id="client-email" name="email" type="email" value="{{ $editing ? 'Ops@myfreight.biz' : '' }}"></div>
            <div class="client-field"><label for="classification">تصنيف العميل</label><select id="classification" name="classification"><option value="">اختر تصنيف العملاء</option><option>عميل نشط</option><option>شركة</option></select></div>
            <div class="client-field"><label for="client-notes">ملاحظات</label><textarea id="client-notes" name="notes" rows="5"></textarea></div>
            <div class="client-field"><label for="attachments">مرفقات</label><label class="upload-zone" for="attachments">☁<br>اسحب الملفات هنا أو اضغط للاختيار</label><input id="attachments" type="file" multiple hidden></div>
            <div class="client-field"><label for="language">لغة العرض</label><select id="language" name="language"><option>العربية</option><option>English</option></select></div>
        </div>
    </section>
</div>
<template id="contact-template"><div class="contact-row"><input aria-label="اسم جهة الاتصال" placeholder="الاسم"><input type="email" aria-label="بريد جهة الاتصال" placeholder="البريد الإلكتروني"><button type="button" class="remove-contact">حذف</button></div></template>
