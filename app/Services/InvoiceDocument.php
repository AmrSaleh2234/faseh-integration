<?php

namespace App\Services;

use App\Models\Invoice;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Contracts\View\View;
use Mpdf\Mpdf;
use NumberFormatter;
use Symfony\Component\HttpFoundation\Response;

class InvoiceDocument
{
    /**
     * @return array<string, mixed>
     */
    public function data(Invoice $invoice): array
    {
        $invoice->loadMissing('client', 'items', 'customFields', 'fasahSync');
        $customFields = $invoice->customFields->mapWithKeys(fn ($field): array => [mb_strtoupper(trim($field->name)) => trim((string) $field->value)]);
        $rows = $invoice->items->map(fn ($item): array => [
            'name' => $item->name,
            'description' => $item->description ?: $item->name,
            'unit_price' => (float) $item->unit_price,
            'quantity' => (float) $item->quantity,
            'subtotal' => max(0, round((float) $item->unit_price * (float) $item->quantity - (float) $item->discount, 2)),
        ]);
        $itemTotal = $rows->sum('subtotal');
        $discount = $invoice->discount_kind === 'percent'
            ? round((float) $invoice->subtotal * (float) $invoice->discount_value / 100, 2)
            : (float) $invoice->discount_value;
        $dueDate = ($invoice->issue_date ?? $invoice->invoice_date)?->copy()->addDays((int) $invoice->payment_terms);
        $taxRates = $invoice->items->map(fn ($item): float => (float) $item->tax_one + (float) $item->tax_two)->unique()->values();
        $taxLabel = $taxRates->count() === 1
            ? 'ضريبة ('.rtrim(rtrim(number_format($taxRates->first(), 2, '.', ''), '0'), '.').'%)'
            : 'ضريبة';

        return [
            'invoice' => $invoice,
            'company' => config('invoice_document.company'),
            'logo' => 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('invoice-template-logo.png'))),
            'currencySymbol' => 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('saudi-riyal-symbol.png'))),
            'fasahNumber' => $invoice->fasahSync?->fasah_invoice_number,
            'banks' => array_filter(config('invoice_document.banks', []), fn (array $bank): bool => filled($bank['iban'] ?? null)),
            'details' => array_filter([
                'DEBIT INVOICE#' => $customFields->get('DEBIT INVOICE#'),
                'JOB#' => $customFields->get('JOB#'),
                'JOB DATE' => $customFields->get('JOB DATE'),
                'BL#' => $customFields->get('BL#') ?: ($invoice->fasah_meta['bill_of_lading'] ?? null),
                'VESSEL' => $customFields->get('VESSEL'),
                'VOY' => $customFields->get('VOY'),
                'ARRIVAL DATE' => $customFields->get('ARRIVAL DATE'),
                'FROM' => $customFields->get('FROM'),
                'TO' => $customFields->get('TO'),
            ], fn ($value): bool => filled($value)),
            'rows' => $rows,
            'itemTotal' => $itemTotal,
            'discount' => $discount,
            'dueDate' => $dueDate,
            'taxLabel' => $taxLabel,
            'balanceWords' => $this->balanceInWords($invoice->balance),
            'qrCode' => $this->qrCode($invoice),
        ];
    }

    public function preview(Invoice $invoice): View
    {
        return view('invoices.document', $this->data($invoice));
    }

    public function download(Invoice $invoice): Response
    {
        abort_unless($invoice->status === 'issued', 404);

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 14,
            'margin_right' => 14,
            'margin_top' => 11.3,
            'margin_bottom' => 14,
            'default_font' => 'dejavusans',
            'tempDir' => storage_path('framework/cache'),
        ]);
        $pdf->autoScriptToLang = true;
        $pdf->autoLangToFont = true;
        $pdf->WriteHTML(view('invoices.document', $this->data($invoice) + ['forPdf' => true])->render());

        return response($pdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-'.$invoice->number.'.pdf"',
        ]);
    }

    private function qrCode(Invoice $invoice): ?string
    {
        $company = config('invoice_document.company');
        $vatNumber = (string) ($company['vat_number'] ?? '');
        if ($invoice->status !== 'issued' || ! preg_match('/^[0-9]{15}$/', $vatNumber) || ! $invoice->issued_at) {
            return null;
        }

        $values = [
            1 => (string) $company['name'],
            2 => $vatNumber,
            3 => $invoice->issued_at->copy()->setTimezone('Asia/Riyadh')->format('Y-m-d\TH:i:sP'),
            4 => number_format((float) $invoice->total, 2, '.', ''),
            5 => number_format((float) $invoice->tax_total, 2, '.', ''),
        ];
        $tlv = '';
        foreach ($values as $tag => $value) {
            $length = strlen($value);
            if ($length > 255) {
                return null;
            }
            $tlv .= chr($tag).chr($length).$value;
        }

        $result = (new PngWriter)->write(new QrCode(
            data: base64_encode($tlv),
            encoding: new Encoding('UTF-8'),
            size: 180,
            margin: 4,
        ));

        return $result->getDataUri();
    }

    private function balanceInWords(float $balance): string
    {
        if (! extension_loaded('intl')) {
            return number_format($balance, 2).' ريال سعودي';
        }

        $formatter = new NumberFormatter('ar_SA', NumberFormatter::SPELLOUT);
        $riyals = (int) floor($balance);
        $halalas = (int) round(($balance - $riyals) * 100);
        if ($halalas === 100) {
            $riyals++;
            $halalas = 0;
        }

        $words = 'فقط '.$formatter->format($riyals).' ريال سعودي';
        if ($halalas > 0) {
            $words .= ' و'.$formatter->format($halalas).' هللة';
        }

        return $words;
    }
}
