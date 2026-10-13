<?php

namespace App\Http\Requests;

class InvoiceFormRequest extends ArabicFormRequest
{
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:draft,issue,metadata'],
            'invoice_number' => ['nullable', 'integer', 'min:1', 'max:999999999999999'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'sales_staff_id' => ['nullable', 'exists:staff,id'],
            'invoice_date' => ['nullable', 'date'],
            'issue_date' => ['nullable', 'date'],
            'payment_terms' => ['nullable', 'integer', 'min:0'],
            'delivery_method' => ['nullable', 'in:طباعة,بريد إلكتروني'],
            'notes' => ['nullable', 'string'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_kind' => ['nullable', 'in:amount,percent'],
            'adjustment' => ['nullable', 'numeric'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'deposit_kind' => ['nullable', 'in:amount,percent'],
            'deposit_paid' => ['sometimes', 'boolean'],
            'insurance_visible' => ['sometimes', 'boolean'],
            'insurance_expenses' => ['nullable', 'numeric', 'min:0'],
            'insurance_company' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_one' => ['nullable', 'in:0,15'],
            'items.*.tax_two' => ['nullable', 'in:0,15'],
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*.name' => ['nullable', 'string', 'max:255'],
            'custom_fields.*.value' => ['nullable', 'string'],
            'fasah_type' => ['nullable', 'in:general,billoflading,declaration,custombroker,importer,shippingagent'],
            'fasah_meta' => ['nullable', 'array'],
            'fasah_meta.*' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'طريقة الحفظ', 'invoice_number' => 'رقم الفاتورة', 'client_id' => 'العميل', 'sales_staff_id' => 'مسؤول المبيعات',
            'invoice_date' => 'تاريخ الفاتورة', 'issue_date' => 'تاريخ الإصدار', 'payment_terms' => 'شروط الدفع بالأيام',
            'delivery_method' => 'طريقة التسليم', 'discount_value' => 'الخصم', 'discount_kind' => 'نوع الخصم',
            'adjustment' => 'التسوية', 'deposit_amount' => 'الدفعة المقدمة', 'deposit_kind' => 'نوع الدفعة المقدمة',
            'items' => 'بنود الفاتورة', 'items.*.name' => 'اسم البند', 'items.*.unit_price' => 'سعر البند',
            'items.*.quantity' => 'كمية البند', 'items.*.discount' => 'خصم البند',
            'items.*.tax_one' => 'الضريبة الأولى', 'items.*.tax_two' => 'الضريبة الثانية',
            'fasah_type' => 'نوع فاتورة Fasah', 'fasah_meta.port' => 'رمز الميناء',
            'fasah_meta.bill_of_lading' => 'رقم بوليصة الشحن', 'fasah_meta.shipment_type' => 'نوع الشحنة',
            'fasah_meta.doc_ref_no' => 'مرجع المستند', 'fasah_meta.carrier_manifest' => 'رقم بيان الناقل',
            'fasah_meta.carrier_manifest_date' => 'تاريخ بيان الناقل',
            'fasah_meta.declaration_number' => 'رقم البيان الجمركي', 'fasah_meta.declaration_date' => 'تاريخ البيان الجمركي',
            'fasah_meta.declaration_type' => 'نوع البيان الجمركي', 'fasah_meta.customs_broker_license' => 'رخصة المخلص',
            'fasah_meta.license_type' => 'نوع الرخصة', 'fasah_meta.importer_number' => 'رقم المستورد',
            'fasah_meta.shipping_agent_number' => 'رقم وكيل الشحن', 'attachments.*' => 'المرفق',
        ];
    }
}
