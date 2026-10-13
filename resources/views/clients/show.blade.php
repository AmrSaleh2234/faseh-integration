@extends('layouts.admin')
@section('title', 'عرض العميل - Titanic Ways')
@section('section-name', 'العملاء')
@section('page-name', 'عرض العميل')
@push('styles')<link rel="stylesheet" href="{{ asset('clients.css') }}">@endpush
@section('page-actions')<div class="actions client-show-actions"><div class="client-page-title"><strong>{{ $client->business_name }}</strong><span>#{{ $client->code }}</span></div>@can('invoices.view')<div class="client-balance"><small>الرصيد المستحق</small><strong>{{ number_format($client->invoices->sum(fn($invoice) => $invoice->balance),2) }} SAR</strong></div>
@endcan
</div>@endsection
@section('content')<section class="client-profile panel"><div class="client-profile-main"><span class="client-avatar color-0">{{ mb_substr($client->business_name,0,1) }}</span><div><h1>{{ $client->business_name }}</h1><p>⌖ {{ $client->city }}</p></div></div><div class="client-profile-links"><span>{{ $client->email }}</span><span>{{ $client->mobile }}</span></div></section>
<section class="app-feedback {{ empty($fasahIssues) ? '' : 'error' }}" role="status"><strong>{{ empty($fasahIssues) ? 'جاهز لفاتورة Fasah عامة' : 'بيانات Fasah العامة تحتاج استكمالًا:' }}</strong>@if($fasahIssues)<ul>@foreach($fasahIssues as $issue)<li>{{ $issue }}</li>@endforeach</ul>@can('clients.edit')<a href="{{ route('clients.edit', $client) }}">تعديل بيانات العميل</a>@endcan @endif</section>
<div class="client-toolbar">@can('clients.edit')<a class="btn gray small" href="{{ route('clients.edit',$client) }}">✎ تعديل</a>
@endcan
 @can('invoices.create')<a class="btn gray small" href="{{ route('invoices.create',['client_id'=>$client->id]) }}">＋ فاتورة</a>
@endcan
</div>
<section class="client-detail panel" style="padding:24px"><h2>بيانات العميل</h2><dl class="client-details-grid"><div><dt>النوع</dt><dd>{{ $client->type === 'business' ? 'تجاري' : 'فردي' }}</dd><dt>المدينة</dt><dd>{{ $client->city ?: '—' }}</dd><dt>الدولة</dt><dd>{{ $client->country ?: '—' }}</dd></div><div><dt>الكود</dt><dd>#{{ $client->code }}</dd><dt>البريد</dt><dd>{{ $client->email ?: '—' }}</dd><dt>الهاتف</dt><dd>{{ $client->mobile ?: '—' }}</dd></div></dl>
@can('invoices.view')
<h2>الفواتير ({{ $client->invoices->count() }})</h2>@forelse($client->invoices as $invoice)<p><a href="{{ route('invoices.show',$invoice) }}">فاتورة #{{ $invoice->number ?? 'مسودة '.$invoice->id }}</a> — {{ $invoice->display_status }} — {{ number_format($invoice->total,2) }} SAR</p>
@empty
<p>لا توجد فواتير لهذا العميل.</p>
@endforelse
@endcan

<h2>جهات الاتصال</h2>@forelse($client->contacts as $contact)<p>{{ $contact->name }} — {{ $contact->email }}</p>
@empty
<p>لا توجد جهات اتصال.</p>
@endforelse

<h2>المرفقات</h2>@forelse($client->attachments as $file)<p><a href="{{ route('attachments.download',$file) }}">{{ $file->original_name }}</a></p>
@empty
<p>لا توجد مرفقات.</p>
@endforelse
</section>
@can('clients.delete')<form method="post" action="{{ route('clients.destroy',$client) }}" onsubmit="return confirm('أرشفة العميل؟')">@csrf @method('DELETE')<button class="btn gray small" type="submit">أرشفة العميل</button></form>
@endcan

@endsection
