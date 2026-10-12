@extends('layouts.admin')
@section('title', 'العملاء - Titanic Ways')
@section('section-name', 'العملاء')
@section('page-name', 'إدارة العملاء')
@push('styles')<link rel="stylesheet" href="{{ asset('clients.css') }}">@endpush
@section('page-actions')
<div class="actions client-actions">@can('clients.create')<a class="btn green" href="{{ route('clients.create') }}">＋ أضف العميل</a>
@endcan
 <span>{{ $clients->total() }} عميل</span></div>
@endsection
@section('content')
<section class="client-filter panel"><div class="client-filter-head">بحث وتصفية</div><form method="get" action="{{ route('clients.index') }}" class="client-filter-grid" style="padding:16px;display:flex;gap:12px;flex-wrap:wrap"><label>الاسم<input name="name" value="{{ request('name') }}" placeholder="اسم العميل"></label><label>النوع<select name="type"><option value="">كل الأنواع</option><option value="business" @selected(request('type') === 'business')>تجاري</option><option value="individual" @selected(request('type') === 'individual')>فردي</option></select></label><label>التصنيف<input name="classification" value="{{ request('classification') }}"></label><button class="btn gray small" type="submit">بحث</button><a class="btn gray small" href="{{ route('clients.index') }}">مسح</a></form></section>
<section class="client-list panel"><div class="client-list-head"><strong>العميل</strong><strong>بيانات الاتصال</strong><strong>الحالة</strong><strong>الإجراءات</strong></div>
@forelse($clients as $client)
<div class="client-list-row"><div class="client-identity"><span class="client-avatar color-{{ $loop->index % 4 }}">{{ mb_substr($client->business_name,0,1) }}</span><div><a href="{{ route('clients.show', $client) }}">{{ $client->business_name }}</a><small>#{{ $client->code }}</small></div></div><div class="client-contact-cell"><span>{{ $client->mobile }}</span><span>{{ $client->email }}</span><span>{{ $client->city }}</span></div><div class="client-owner">نشط</div><div><a href="{{ route('clients.show', $client) }}">عرض</a> @can('clients.edit')<a href="{{ route('clients.edit', $client) }}">تعديل</a>
@endcan
</div></div>

@empty
<p class="client-empty" style="padding:32px;text-align:center">لا يوجد عملاء بعد.</p>
@endforelse

</section><div style="margin-top:16px">{{ $clients->links() }}</div>
@endsection
