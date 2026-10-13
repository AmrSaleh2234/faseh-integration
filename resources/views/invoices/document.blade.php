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
        body { margin: 0; color: #111; background: #fff; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
        table { border-collapse: collapse; }
        .sheet { width: 100%; }
        .top, .bill, .bottom, .banks { width: 100%; table-layout: fixed; }
        .top td, .bill td, .bottom td, .banks td { vertical-align: top; }
        .top-left { width: 56%; }
        .top-right { width: 44%; text-align: right; }
        .title { margin: 1px 0 25px; font-family: DejaVu Sans, sans-serif; font-size: 20px; font-weight: normal; }
        .invoice-no { margin: 0 0 24px; font-size: 15px; font-weight: bold; }
        .meta { width: 100%; }
        .meta td { padding: 2px 0; vertical-align: top; }
        .meta td:first-child { width: 124px; font-weight: bold; }
        .meta .date-label { font-weight: normal !important; }
        .brand-logo { display: block; width: 215px; height: 140px; margin-left: auto; }
        .brand-word { margin: -15px 0 10px; color: #2c3d50; font-size: 24px; font-weight: bold; line-height: 1; }
        .brand-word span { color: #b99168; font-weight: normal; }
        .company-info { display: block; width: 235px; margin-left: auto; text-align: left; direction: ltr; line-height: 1.8; }
        .company-info strong { display: block; margin-bottom: 3px; }
        .divider { border: 0; border-top: 1px solid #bcc2c8; margin: 24px 0 18px; }
        .bill-left { width: 62%; padding-left: 16px; }
        .bill-right { width: 38%; }
        .bill-label { display: block; margin-bottom: 6px; }
        .client-name { font-weight: bold; line-height: 1.7; }
        .client-extra { margin-top: 20px; line-height: 1.8; }
        .amount-columns { width: 100%; }
        .amount-columns td { width: 50%; padding: 0 6px 21px 0; }
        .amount-value { display: block; margin-top: 7px; font-size: 13px; font-weight: bold; white-space: nowrap; }
        .amount-columns .large { font-size: 16px; }
        .items { width: 100%; margin-top: 22px; }
        .items th { padding: 7px 8px 10px; text-align: left; font-weight: normal; border-bottom: 1px solid #dfe3e8; }
        .items td { padding: 10px 8px; vertical-align: top; border-bottom: 1px solid #edf0f3; }
        .items th:nth-child(1) { width: 28%; }
        .items th:nth-child(2) { width: 28%; }
        .items th:nth-child(3) { width: 17%; }
        .items th:nth-child(4) { width: 11%; }
        .items th:nth-child(5) { width: 16%; text-align: right; }
        .items td:last-child { text-align: right; }
        .bottom { margin-top: 25px; }
        .qr-cell { width: 42%; }
        .qr-cell img { width: 90px; height: 90px; }
        .totals { width: 100%; }
        .totals td { padding: 4px 0 10px; }
        .totals td:first-child { color: #4b5661; }
        .totals td:last-child { width: 110px; text-align: right; white-space: nowrap; }
        .totals .strong td { color: #111; font-weight: bold; }
        .note { margin: 42px 0 26px; line-height: 1.9; white-space: pre-wrap; }
        .banks td { width: 50%; padding-right: 18px; font-style: italic; line-height: 1.9; }
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
            .top-right { text-align: left; padding-top: 25px; }
            .brand-logo, .company-info { margin-left: 0; }
            .divider { margin-top: 18px; }
            .bill-left { padding-left: 0; }
            .bill-right { padding-top: 18px; }
            .items-scroll { overflow-x: auto; }
            .items { min-width: 590px; }
            .bottom .qr-cell { width: 32%; }
            .totals td:last-child { width: 94px; }
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
            <p class="invoice-no">Invoice No #{{ $invoice->number ?? 'مسودة '.$invoice->id }}</p>
            <table class="meta">
                <tr><td class="date-label">Invoice Date</td><td>{{ $invoice->invoice_date?->format('d/m/Y') ?? '—' }}</td></tr>
                @foreach($details as $label => $value)
                    <tr><td>{{ $label }}</td><td>{{ $value }}</td></tr>
                @endforeach
            </table>
        </td>
        <td class="top-right">
            <img class="brand-logo" src="{{ $logo }}" alt="Titanic Ways">
            <div class="brand-word">Titanic<span>Ways</span></div>
            <div class="company-info">
                <strong>{{ $company['name'] }}</strong>
                @if($company['registration'])<div>CR: {{ $company['registration'] }}</div>@endif
                @if($company['vat_number'])<div>VAT: {{ $company['vat_number'] }}</div>@endif
                @if($company['address_line_one'])<div>{{ $company['address_line_one'] }}</div>@endif
                @if($company['address_line_two'])<div>{{ $company['address_line_two'] }}</div>@endif
                @if($company['phone'])<div>{{ $company['phone'] }}</div>@endif
            </div>
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
            <table class="amount-columns"><tr>
                <td>Total Amount<div class="amount-value">SAR {{ number_format((float) $invoice->total, 2) }}</div></td>
                <td>Balance Due<div class="amount-value large">SAR {{ number_format($invoice->balance, 2) }}</div></td>
            </tr><tr>
                <td>Paid<div class="amount-value">SAR {{ number_format((float) $invoice->amount_paid, 2) }}</div></td>
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
        <td class="qr-cell">@if($qrCode)<img src="{{ $qrCode }}" width="90" height="90" alt="رمز بيانات الفاتورة QR">@endif</td>
        <td>
            <table class="totals">
                <tr><td>Item Total</td><td>SAR {{ number_format($itemTotal, 2) }}</td></tr>
                <tr><td>VAT</td><td>SAR {{ number_format((float) $invoice->tax_total, 2) }}</td></tr>
                @if($discount > 0)<tr><td>Discount</td><td>− SAR {{ number_format($discount, 2) }}</td></tr>@endif
                @if((float) $invoice->adjustment !== 0.0)<tr><td>Adjustment</td><td>SAR {{ number_format((float) $invoice->adjustment, 2) }}</td></tr>@endif
                @if((float) $invoice->insurance_expenses > 0)<tr><td>Insurance</td><td>SAR {{ number_format((float) $invoice->insurance_expenses, 2) }}</td></tr>@endif
                <tr class="strong"><td>Total</td><td>SAR {{ number_format((float) $invoice->total, 2) }}</td></tr>
                <tr><td>Paid</td><td>SAR {{ number_format((float) $invoice->amount_paid, 2) }}</td></tr>
                <tr class="strong"><td>Balance Due</td><td>SAR {{ number_format($invoice->balance, 2) }}</td></tr>
            </table>
        </td>
    </tr></table>

    <div class="note" dir="rtl">الرصيد المستحق: {{ number_format($invoice->balance, 2) }} ريال سعودي@if($invoice->notes)

{{ str_replace('{%unpaid_spelled%}', number_format($invoice->balance, 2).' ريال سعودي', $invoice->notes) }}@endif</div>

    @if($banks)
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
    @endif
</main>
</body>
</html>
