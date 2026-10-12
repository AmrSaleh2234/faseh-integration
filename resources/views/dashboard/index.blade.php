@extends('layouts.admin')
@section('title', 'لوحة التحكم - Titanic Ways')
@section('section-name', 'لوحة التحكم')
@section('page-name', 'الملخص')
@push('styles')<link rel="stylesheet" href="{{ asset('dashboard.css') }}">@endpush
@section('page-actions')<div class="actions dashboard-actions"><span>ملخص البيانات المحلية</span>@can('invoices.create')<a class="btn green" href="{{ route('invoices.create') }}">＋ إنشاء فاتورة</a>
@endcan
</div>@endsection
@section('content')
<section class="dashboard-welcome"><div><span class="dashboard-date">{{ now()->format('d/m/Y') }}</span><h1>أهلًا {{ auth()->user()->name }}، مرحبًا بعودتك!</h1><p>هذه نظرة سريعة على بيانات المشروع المحفوظة في MySQL.</p></div></section>
<section class="dashboard-searches" aria-label="بحث سريع">@can('invoices.view')<form action="{{ route('invoices.index') }}" method="get" class="dashboard-search panel"><label>البحث عن الفواتير</label><div><input name="q" placeholder="رقم الفاتورة أو اسم العميل"><button type="submit" aria-label="بحث في الفواتير">⌕</button></div></form>
@endcan
 @can('clients.view')<form action="{{ route('clients.index') }}" method="get" class="dashboard-search panel"><label>البحث عن العملاء</label><div><input name="name" placeholder="اسم العميل"><button type="submit" aria-label="بحث في العملاء">⌕</button></div></form>
@endcan
</section>
<nav class="dashboard-shortcuts" aria-label="روابط سريعة">@can('invoices.create')<a href="{{ route('invoices.create') }}"><span class="shortcut-icon blue">▤＋</span><strong>إنشاء فاتورة</strong></a>
@endcan
 @can('invoices.view')<a href="{{ route('invoices.index') }}"><span class="shortcut-icon teal">▤</span><strong>الفواتير</strong></a>
@endcan
 @can('clients.view')<a href="{{ route('clients.index') }}"><span class="shortcut-icon green">♙</span><strong>العملاء</strong></a>
@endcan
 @can('clients.create')<a href="{{ route('clients.create') }}"><span class="shortcut-icon purple">♙＋</span><strong>عميل جديد</strong></a>
@endcan
</nav>
<section class="dashboard-stats" aria-label="الأرقام">
@can('invoices.view')
<div class="dashboard-stat panel"><span>الفواتير</span><strong>{{ $invoiceCount }}</strong></div>
<div class="dashboard-stat panel"><span>مدفوعة</span><strong>{{ $paidCount }}</strong></div>
<div class="dashboard-stat panel"><span>غير مدفوعة</span><strong>{{ $unpaidCount }}</strong></div>
@endcan
@can('clients.view')
<div class="dashboard-stat panel"><span>العملاء</span><strong>{{ $clientCount }}</strong></div>
@endcan
</section>
<div class="dashboard-columns">
@can('invoices.view')
<section class="dashboard-sales panel"><div class="dashboard-panel-head"><div><span class="dashboard-kicker">المبيعات</span><h2>ملخص الفواتير</h2></div><a href="{{ route('invoices.index') }}">عرض الفواتير ←</a>
</div><div class="dashboard-tab-panel"><div class="dashboard-amounts"><div><span>إجمالي الفواتير الصادرة</span><strong>﷼ {{ number_format($totalAmount,2) }}</strong></div><div><span>المدفوع</span><strong>﷼ {{ number_format($paidAmount,2) }}</strong></div><div><span>المستحق</span><strong>﷼ {{ number_format($totalAmount-$paidAmount,2) }}</strong></div></div><div class="dashboard-chart-head"><strong>الفواتير حسب اليوم (SAR)</strong></div><div class="dashboard-chart" role="img" aria-label="إجمالي الفواتير حسب اليوم">@forelse($daily as $day)<div class="dashboard-chart-row"><span>{{ \Illuminate\Support\Carbon::parse($day->invoice_date)->format('d/m') }}</span><div class="dashboard-chart-track"><span class="paid" style="width:{{ $day->total > 0 ? min(100,($day->paid/$day->total)*100) : 0 }}%"></span><span class="unpaid" style="width:{{ $day->total > 0 ? min(100,(($day->total-$day->paid)/$day->total)*100) : 0 }}%"></span></div><span class="dashboard-chart-amount">{{ number_format($day->total,2) }}</span></div>
@empty
<p>لا توجد فواتير صادرة بعد.</p>
@endforelse
</div><h3>أحدث الفواتير</h3>@forelse($recentInvoices as $invoice)<div class="dashboard-invoice-row"><div><strong>{{ $invoice->number ? '#'.$invoice->number : 'مسودة #'.$invoice->id }}</strong><span>{{ $invoice->client?->business_name ?? 'بدون عميل' }}</span></div><div><strong>﷼ {{ number_format($invoice->total,2) }}</strong><span class="dashboard-status">{{ $invoice->display_status }}</span></div>@can('invoices.view')<a href="{{ route('invoices.show',$invoice) }}">عرض</a>
@endcan
</div>
@empty
<p>لا توجد فواتير بعد.</p>
@endforelse
</div></section>
@endcan
<aside class="dashboard-aside">
@if(auth()->user()->permits('staff.view') || auth()->user()->permits('roles.view'))
<section class="panel dashboard-team"><div class="dashboard-panel-head"><div><span class="dashboard-kicker">الفريق</span><h2>الموظفون والأدوار</h2></div></div><div class="dashboard-team-grid">
@can('staff.view')<div><strong>{{ $staffCount }}</strong><span>موظف</span></div>
@endcan
@can('roles.view')<div><strong>{{ $roleCount }}</strong><span>دور</span></div>
@endcan
</div></section>
@endif
@can('activity.view')
<section class="panel dashboard-activity"><div class="dashboard-panel-head"><div><span class="dashboard-kicker">المتابعة</span><h2>أحدث الأنشطة</h2></div><a href="{{ route('settings.activity') }}">عرض الجميع ←</a>
</div>@forelse($activities as $entry)<div class="dashboard-activity-entry"><span class="dashboard-activity-icon">✓</span><div><strong>{{ $entry->event }} · {{ $entry->target }}</strong><small>{{ $entry->created_at->format('d/m/Y H:i') }}</small></div></div>
@empty
<p class="dashboard-activity-empty">لا يوجد نشاط مسجل بعد.</p>
@endforelse
</section>
@endcan
</aside></div>
@endsection
