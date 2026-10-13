@php
    $sync = $invoice->fasahSync;
    $labels = ['pending'=>'قيد المزامنة','sending'=>'جارٍ الإرسال','synced'=>'تمت المزامنة','failed'=>'فشلت المزامنة','uncertain'=>'تحتاج مراجعة'];
@endphp
<section class="fasah-sync-panel panel" id="fasah-sync" data-status-url="{{ route('invoices.fasah.status', $invoice) }}" data-status="{{ $sync?->status ?? 'not_started' }}" aria-live="polite">
    <div>
        <strong>مزامنة Fasah</strong>
        <span class="fasah-sync-badge {{ $sync?->status }}" id="fasah-sync-label">{{ $labels[$sync?->status] ?? 'لم تبدأ' }}</span>
    </div>
    <p id="fasah-sync-number" @if(! $sync?->fasah_invoice_number) hidden @endif>رقم Fasah: <strong>{{ $sync?->fasah_invoice_number }}</strong></p>
    <p id="fasah-sync-message" @if(! $sync?->last_error) hidden @endif>{{ $sync?->last_error }}</p>
    @can('invoices.edit')
        <form id="fasah-retry-form" method="post" action="{{ route('invoices.fasah.retry', $invoice) }}" @if($sync?->status !== 'failed') hidden @endif>@csrf<button class="btn blue small" type="submit">إعادة محاولة المزامنة</button></form>
    @endcan
</section>
