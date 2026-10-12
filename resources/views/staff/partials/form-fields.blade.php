@php($editing = request()->routeIs('staff.edit'))
<section class="staff-form-section panel"><h2>معلومات عامة</h2><div class="staff-fields">
    <label>الاسم الأول <em>*</em><input name="first_name" required value="{{ $editing && request()->route('staff') === '1' ? 'محمد' : '' }}"></label>
    <label>الاسم الأوسط<input name="middle_name"></label>
    <label>اللقب <em>*</em><input name="last_name" required value="{{ $editing && request()->route('staff') === '1' ? 'القرني' : '' }}"></label>
    <label>صورة الموظف<input name="photo" type="file" accept="image/*"></label>
    <label class="staff-wide">ملاحظات<textarea name="notes" rows="4"></textarea></label>
    <label>نوع الموظف<select name="type"><option>مستخدم</option><option>موظف</option></select></label>
    <label>الكود <em>*</em><input name="code" required value="{{ $editing && request()->route('staff') === '1' ? '1' : '' }}"></label>
    <label>البريد الإلكتروني<input name="email" type="email" value="{{ $editing && request()->route('staff') === '1' ? 'info@titanicways.com' : '' }}"></label>
    <label>لغة العرض<select name="language"><option>العربية</option><option>English</option></select></label>
    <label>الدور الوظيفي<select name="role"><option>موظف مبيعات</option><option>موظف</option><option @selected($editing && request()->route('staff') === '1')>Owner</option></select></label>
    <label class="staff-check"><input name="login_allowed" type="checkbox" @checked($editing && request()->route('staff') === '1')> السماح بالدخول إلى النظام</label>
</div></section>

<section class="staff-form-section panel"><h2>معلومات موظف</h2>
    <details open><summary>معلومات شخصية</summary><div class="staff-fields"><label>تاريخ الميلاد<input name="birth_date" type="date"></label><label>النوع<select name="gender"><option value="">اختر النوع</option><option>ذكر</option><option>أنثى</option></select></label><label>الدولة<select name="country"><option>المملكة العربية السعودية</option><option>مصر</option><option>الإمارات العربية المتحدة</option></select></label><label>حالة المواطنة<select name="citizenship"><option value="">اختر</option><option>مواطن</option><option>مقيم</option></select></label><label>رقم بطاقة الهوية<input name="identity_number"></label><label>الجنسية<input name="nationality"></label><label>تاريخ انتهاء الإقامة<input name="residency_expiry" type="date"></label></div></details>
    <details open><summary>معلومات تواصل</summary><div class="staff-fields"><label>رقم الجوال<input name="mobile" inputmode="tel"></label><label>رقم الهاتف<input name="phone" inputmode="tel"></label><label>البريد الإلكتروني الشخصي<input name="personal_email" type="email"></label></div></details>
    <details><summary>العنوان الحالي</summary><div class="staff-fields"><label>رقم المبنى<input name="current_building"></label><label>اسم الشارع<input name="current_street"></label><label>الرقم الفرعي<input name="current_secondary"></label><label>الحي<input name="current_district"></label><label>المدينة<input name="current_city"></label><label>الرمز البريدي<input name="current_postal"></label><label>العنوان المختصر<input name="current_short"></label></div></details>
    <details><summary>العنوان الدائم</summary><div class="staff-fields"><label>رقم المبنى<input name="permanent_building"></label><label>اسم الشارع<input name="permanent_street"></label><label>الحي<input name="permanent_district"></label><label>المدينة<input name="permanent_city"></label><label>الرمز البريدي<input name="permanent_postal"></label></div></details>
</section>

<section class="staff-form-section panel"><h2>معلومات عمل</h2>
    <details open><summary>معلومات وظيفة</summary><div class="staff-fields"><label>المسمى الوظيفي<input name="job_title" placeholder="مثال: مندوب مبيعات"></label><label>قسم<input name="department" placeholder="اختر قسم"></label><label>نوع وظيفة<select name="employment_type"><option value="">اختر نوع وظيفة</option><option>دوام كامل</option><option>دوام جزئي</option></select></label><label>المستوى الوظيفي<input name="job_level"></label><label>تاريخ الالتحاق<input name="join_date" type="date"></label><label>المدير المباشر<select name="manager"><option value="">اختر موظف</option><option>محمد القرني #1</option></select></label></div></details>
    <details><summary>معلومات الحضور</summary><div class="staff-fields"><label>الوردية الأساسية<input name="shift" placeholder="اختر الوردية الأساسية"></label><label>سياسة الإجازات<input name="leave_policy" placeholder="اختر سياسة الإجازات"></label><label>قائمة العطلات<input name="holidays" placeholder="اختر قائمة العطلات"></label></div></details>
</section>
