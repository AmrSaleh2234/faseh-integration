<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ArabicFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'string' => 'يجب أن يكون :attribute نصًا.',
            'numeric' => 'أدخل رقمًا صحيحًا في :attribute.',
            'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
            'boolean' => 'قيمة :attribute غير صالحة.',
            'array' => 'بيانات :attribute غير صالحة.',
            'file' => 'الملف في :attribute غير صالح.',
            'email' => 'أدخل بريدًا إلكترونيًا صحيحًا في :attribute.',
            'date' => 'أدخل تاريخًا صحيحًا في :attribute.',
            'in' => 'القيمة المحددة في :attribute غير صالحة.',
            'exists' => 'القيمة المحددة في :attribute غير موجودة.',
            'unique' => ':attribute مستخدم بالفعل.',
            'min' => 'قيمة :attribute أقل من الحد المسموح (:min).',
            'max' => 'قيمة :attribute تتجاوز الحد المسموح (:max).',
            'gt' => 'يجب أن تكون قيمة :attribute أكبر من :value.',
        ];
    }
}
