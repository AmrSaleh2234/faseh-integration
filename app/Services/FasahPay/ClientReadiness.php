<?php

namespace App\Services\FasahPay;

use App\Models\Client;

class ClientReadiness
{
    public function __construct(private readonly InvoicePayloadBuilder $payloadBuilder) {}

    /** @return array<string, string> */
    public function issues(Client|array $client): array
    {
        $value = fn (string $field): string => trim((string) (is_array($client) ? ($client[$field] ?? '') : ($client->{$field} ?? '')));
        $issues = [];

        if ($value('business_name') === '') {
            $issues['business_name'] = 'أدخل اسم العميل أو المنشأة.';
        }
        if ($value('email') === '' || filter_var($value('email'), FILTER_VALIDATE_EMAIL) === false) {
            $issues['email'] = 'أدخل بريدًا إلكترونيًا صحيحًا لاستلام فاتورة Fasah العامة.';
        }
        if ($this->payloadBuilder->normalizeConsumerMobile($value('mobile') ?: $value('phone')) === null) {
            $issues['mobile'] = 'أدخل جوالًا سعوديًا صحيحًا، مثل 05XXXXXXXX أو +9665XXXXXXXX.';
        }
        $vat = $value('tax_number');
        if ($vat !== '' && preg_match('/^[0-9]{15}$/D', $vat) !== 1) {
            $issues['tax_number'] = 'الرقم الضريبي اختياري؛ إذا أدخلته فيجب أن يتكون من 15 رقمًا.';
        }

        return $issues;
    }
}
