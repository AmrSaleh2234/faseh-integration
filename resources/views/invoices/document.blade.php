<!doctype html>
<html lang="ar">
<head>
    <meta charset="utf-8">
    <title>فاتورة #{{ $invoice->number ?? $invoice->id }} - Titanic Ways</title>
    <style>
        @unless($forPdf ?? false)
            @page { size: A4 portrait; margin: 36px 42px 42px; }
        @endunless
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; background: #fff; font-family: DejaVu Sans, sans-serif; font-size: 9.6pt; line-height: 1.5; }
        table { border-collapse: collapse; }
        .sheet { width: 100%; }
        .top, .bill, .bottom, .banks { width: 100%; table-layout: fixed; }
        .top td, .bill td, .bottom td, .banks td { vertical-align: top; }
        .top { width: 100%; }
        .top-left { width: 66%; }
        .top-right { width: 34%; text-align: left; padding-bottom: 55pt; }
        .title { margin: 0; font-size: 16pt; font-weight: normal; }
        .invoice-no { margin: 0; font-size: 11pt; font-weight: bold; }
        .fp-number { margin-left: 14pt; font-size: 8pt; font-weight: normal; }
        .meta { width: 100%; table-layout: fixed; }
        .meta td { padding: 2pt 0; vertical-align: top; }
        .meta td:first-child { width: 82pt; font-weight: bold; }
        .meta .date-label, .meta .date-value { font-weight: normal !important; }
        .brand-logo { display: block; width: 160pt; height: 122.5pt; }
        .company-info { width: 164pt; text-align: left; direction: ltr; }
        .company-info td { padding: 0 0 4pt; }
        .company-info .company-name td { padding-bottom: 8pt; }
        .divider { border: 0; border-top: 0.6pt solid #b9b9b9; margin: 3pt 0 14pt; }
        .bill { height: 88pt; }
        .bill-left { width: 65%; padding-left: 16pt; }
        .bill-right { width: 35%; }
        .bill-label { display: block; margin-bottom: 5pt; }
        .client-name { line-height: 1.55; }
        .client-extra { padding-top: 24pt; line-height: 1.6; }
        .amount-columns { width: 100%; }
        .amount-columns { table-layout: fixed; }
        .amount-columns td { padding: 0 5pt 20pt 0; white-space: nowrap; }
        .amount-columns td:first-child { width: 61pt; }
        .amount-value { display: block; margin-top: 4pt; white-space: nowrap; }
        .amount-columns .large { font-size: 13pt; font-weight: bold; }
        .items { width: 100%; margin-top: 22pt; }
        .items th { padding: 6pt 7pt 5pt; text-align: left; font-weight: normal; border-bottom: 0.35pt solid #edf0f3; }
        .items td { padding: 7pt; vertical-align: top; border-bottom: 0.35pt solid #edf0f3; }
        .items th:nth-child(1) { width: 29.5%; }
        .items th:nth-child(2) { width: 28.5%; }
        .items th:nth-child(3) { width: 13.5%; }
        .items th:nth-child(4) { width: 13.5%; }
        .items th:nth-child(5) { width: 15%; text-align: right; }
        .items td:last-child { text-align: right; }
        .bottom { margin-top: 18pt; }
        .qr-cell { width: 43.5%; padding-left: 5pt; }
        .qr-cell img { width: 59pt; height: 59pt; }
        .totals { width: 290pt; table-layout: fixed; }
        .totals td { padding: 3pt 0 9pt; }
        .totals td:first-child { color: #4b5661; }
        .totals-spacer { width: 125pt; }
        .totals td:last-child { width: 75pt; text-align: right; white-space: nowrap; }
        .totals .strong td { color: #111; font-weight: bold; }
        .footer { width: 100%; margin-top: 35pt; table-layout: fixed; }
        .footer td { vertical-align: top; }
        .note { padding: 0 0 12pt; line-height: 1.8; }
        .currency { vertical-align: middle; }
        .banks td { width: 50%; padding-right: 18pt; font-style: italic; line-height: 1.6; }
        .banks strong { font-weight: bold; }
        .pdf-actions { display: none; }
        @unless($forPdf ?? false)
        @media screen {
            body { background: #e6ebf1; }
            .pdf-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; padding: 14px; font: 14px Arial, sans-serif; }
            .pdf-actions a, .pdf-actions button { border: 0; background: #188d61; color: white; text-decoration: none; padding: 10px 18px; cursor: pointer; }
            .pdf-actions a:first-child { background: #526177; }
            .sheet { width: min(794px, 100%); min-height: 1123px; margin: 0 auto 24px; padding: 48px 54px; background: white; box-shadow: 0 8px 28px #34435224; }
        }
        @media screen and (max-width: 700px) {
            .sheet { min-height: 0; padding: 24px 18px; }
            .top, .bill { display: block; }
            .top > tbody > tr > td, .bill > tbody > tr > td { display: block; width: 100%; }
            .top, .bill { height: auto; }
            .top-right { padding-top: 25px; padding-bottom: 0; }
            .divider { margin-top: 18px; }
            .bill-left { padding-left: 0; }
            .bill-right { padding-top: 18px; }
            .items-scroll { overflow-x: auto; }
            .items { min-width: 590px; }
            .bottom, .bottom > tbody > tr > td { display: block; width: 100%; }
            .bottom .qr-cell { width: 100%; }
            .totals { width: 100%; }
            .banks, .banks > tbody > tr > td { display: block; width: 100%; }
            .banks > tbody > tr > td { padding-bottom: 16px; }
        }
        @media print { .pdf-actions { display: none; } }
        @endunless
        @if($forPdf ?? false)
            .sheet { width: 100%; min-height: 0; margin: 0; padding: 0; background: white; box-shadow: none; }
        @endif
    </style>
</head>
<body>
@unless($forPdf ?? false)
    <nav class="pdf-actions" aria-label="أدوات الفاتورة">
        <a href="{{ route('invoices.show', $invoice) }}">رجوع للفاتورة</a>
        @if($invoice->status === 'issued')<a href="{{ route('invoices.pdf', $invoice) }}">تنزيل PDF</a>@endif
        <button type="button" onclick="window.print()">طباعة</button>
    </nav>
@endunless
<main class="sheet">
    <table class="top"><tr>
        <td class="top-left">
            <h1 class="title">VAT Invoice</h1>
            <br><br>
            <p class="invoice-no">Invoice No #{{ $invoice->number ?? 'مسودة '.$invoice->id }}@if($fasahNumber)<span class="fp-number">&nbsp;&nbsp;&nbsp;FP: {{ $fasahNumber }}</span>@endif</p>
            <br><br>
            <table class="meta">
                <colgroup><col style="width: 82pt"><col></colgroup>
                <tr><td class="date-label" width="110">Invoice Date</td><td></td></tr>
                <tr><td class="date-value" width="110">{{ $invoice->invoice_date?->format('d/m/Y') ?? '—' }}</td><td></td></tr>
                @foreach($details as $label => $value)
                    <tr>
                        <td width="110">
                            @if($label === 'DEBIT INVOICE#')
                                DEBIT<br>INVOICE#
                            @elseif($label === 'ARRIVAL DATE')
                                ARRIVAL<br>DATE
                            @else
                                {{ $label }}
                            @endif
                        </td>
                        <td>{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </td>
        <td class="top-right">
            <img class="brand-logo" src="{{ $logo }}" alt="Titanic Ways">
            <br><br>
            <table class="company-info">
                <tr class="company-name"><td><strong>{{ $company['name'] }}</strong></td></tr>
                @if($company['registration'])<tr><td>CR: {{ $company['registration'] }}</td></tr>@endif
                @if($company['vat_number'])<tr><td>VAT: {{ $company['vat_number'] }}</td></tr>@endif
                @if($company['address_line_one'])<tr><td>{{ $company['address_line_one'] }}</td></tr>@endif
                @if($company['address_line_two'])<tr><td>{{ $company['address_line_two'] }}</td></tr>@endif
                @if($company['phone'])<tr><td>{{ $company['phone'] }}</td></tr>@endif
            </table>
        </td>
    </tr></table>

    <hr class="divider">

    <table class="bill"><tr>
        <td class="bill-left">
            <span class="bill-label">Bill To:</span>
            <div class="client-name" dir="auto">{{ $invoice->client?->business_name ?? '—' }}</div>
            <div class="client-extra" dir="rtl">
                @if($invoice->client?->city)<div>{{ $invoice->client->city }}</div>@endif
                @if($invoice->client?->tax_number)<div>الرقم الضريبي: {{ $invoice->client->tax_number }}</div>@endif
                @if($invoice->client?->registration)<div>السجل التجاري: {{ $invoice->client->registration }}</div>@endif
                @if($invoice->client?->address)
                    <div>{{ implode('، ', array_filter([$invoice->client->address['street'] ?? null, $invoice->client->address['district'] ?? null, $invoice->client->address['postal'] ?? null])) }}</div>
                @endif
            </div>
        </td>
        <td class="bill-right">
            <table class="amount-columns"><colgroup><col style="width: 61pt"><col style="width: 119pt"></colgroup><tr>
                <td width="82">Total Amount<div class="amount-value"><img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format((float) $invoice->total, 2) }}</div></td>
                <td>Balance Due<div class="amount-value large"><img class="currency" src="{{ $currencySymbol }}" width="17" height="19" alt="ريال سعودي"> {{ number_format($invoice->balance, 2) }}</div></td>
            </tr><tr>
                <td>Paid<div class="amount-value"><img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format((float) $invoice->amount_paid, 2) }}</div></td>
                <td>Due Date<div class="amount-value">{{ $dueDate?->format('d/m/Y') ?? '—' }}</div></td>
            </tr></table>
        </td>
    </tr></table>

    <div class="items-scroll"><table class="items">
        <thead><tr><th>Item</th><th>Description</th><th>Unit Price</th><th>Quantity</th><th>Subtotal</th></tr></thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td dir="auto">{{ $row['name'] }}</td>
                    <td dir="auto">{{ $row['description'] }}</td>
                    <td>{{ number_format($row['unit_price'], 2) }}</td>
                    <td>{{ rtrim(rtrim(number_format($row['quantity'], 2, '.', ''), '0'), '.') }}</td>
                    <td>{{ number_format($row['subtotal'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">لا توجد بنود.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    <table class="bottom"><tr>
        <td class="qr-cell">@if($qrCode)<img src="{{ $qrCode }}" width="79" height="79" alt="رمز بيانات الفاتورة QR">@endif</td>
        <td>
            <table class="totals">
                <tr><td>Item Total</td><td class="totals-spacer"></td><td><img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format($itemTotal, 2) }}</td></tr>
                <tr><td dir="rtl">{{ $taxLabel }}</td><td class="totals-spacer"></td><td><img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format((float) $invoice->tax_total, 2) }}</td></tr>
                @if($discount > 0)<tr><td>Discount</td><td class="totals-spacer"></td><td>− <img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format($discount, 2) }}</td></tr>@endif
                @if((float) $invoice->adjustment !== 0.0)<tr><td>Adjustment</td><td class="totals-spacer"></td><td><img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format((float) $invoice->adjustment, 2) }}</td></tr>@endif
                @if((float) $invoice->insurance_expenses > 0)<tr><td>Insurance</td><td class="totals-spacer"></td><td><img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format((float) $invoice->insurance_expenses, 2) }}</td></tr>@endif
                <tr class="strong"><td>Total</td><td class="totals-spacer"></td><td><img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format((float) $invoice->total, 2) }}</td></tr>
                <tr><td>Paid</td><td class="totals-spacer"></td><td><img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format((float) $invoice->amount_paid, 2) }}</td></tr>
                <tr class="strong"><td>Balance Due</td><td class="totals-spacer"></td><td><img class="currency" src="{{ $currencySymbol }}" width="12" height="13" alt="ريال سعودي"> {{ number_format($invoice->balance, 2) }}</td></tr>
            </table>
        </td>
    </tr></table>

    <table class="footer">
        <tr><td class="note" dir="rtl">
            الرصيد المستحق: {{ $balanceWords }}
            @if($invoice->notes)<br>{{ str_replace('{%unpaid_spelled%}', $balanceWords, $invoice->notes) }}@endif
        </td></tr>
        @if($banks)
            <tr><td>
                <table class="banks"><tr>
                    @foreach($banks as $bank)
                        <td>
                            <div><strong>{{ $bank['label'] }}</strong></div>
                            <div>{{ $bank['account_name'] }}</div>
                            <div>IBAN: {{ $bank['iban'] }}</div>
                            @if($bank['swift'])<div>Swift Code: {{ $bank['swift'] }}</div>@endif
                            @if($bank['bank'])<div>{{ $bank['bank'] }}</div>@endif
                        </td>
                    @endforeach
                </tr></table>
            </td></tr>
        @endif
    </table>
</main>
</body>
</html>
