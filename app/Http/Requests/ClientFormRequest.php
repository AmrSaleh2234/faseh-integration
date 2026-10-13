<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ClientFormRequest extends ArabicFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('clients', 'code')->ignore($this->route('client'))],
            'type' => ['required', 'in:individual,business'],
            'business_name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'classification' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'registration' => ['nullable', 'string', 'max:100'],
            'billing' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:50'],
            'government' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
            'building' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'secondary_number' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'postal' => ['nullable', 'string', 'max:50'],
            'short_address' => ['nullable', 'string', 'max:255'],
            'second_address' => ['nullable', 'string'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.name' => ['nullable', 'string', 'max:255'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'كود العميل', 'type' => 'نوع العميل', 'business_name' => 'اسم العميل أو المنشأة',
            'first_name' => 'الاسم الأول', 'last_name' => 'الاسم الأخير', 'phone' => 'الهاتف',
            'mobile' => 'الجوال', 'email' => 'البريد الإلكتروني', 'city' => 'المدينة', 'country' => 'الدولة',
            'classification' => 'تصنيف العميل', 'tax_number' => 'الرقم الضريبي', 'registration' => 'السجل التجاري',
            'billing' => 'طريقة إرسال الفواتير', 'language' => 'لغة العرض', 'government' => 'الجهة الحكومية',
            'building' => 'رقم المبنى', 'street' => 'اسم الشارع', 'secondary_number' => 'الرقم الإضافي',
            'district' => 'الحي', 'postal' => 'الرمز البريدي', 'short_address' => 'العنوان المختصر',
            'contacts.*.name' => 'اسم جهة الاتصال', 'contacts.*.email' => 'بريد جهة الاتصال',
            'attachments.*' => 'المرفق',
        ];
    }
}
