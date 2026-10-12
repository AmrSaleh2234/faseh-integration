@extends('layouts.admin')
@section('title', 'سجل النشاط - Titanic Ways')
@section('section-name', 'الإعدادات')
@section('page-name', 'سجل النشاط')
@push('styles')<link rel="stylesheet" href="{{ asset('settings.css') }}">@endpush
@section('page-actions')<div class="actions settings-actions">@can('roles.view')<a class="settings-action-link" href="{{ route('settings.roles') }}">→ الأدوار والصلاحيات</a>
@endcan
</div>@endsection
@section('content')
<div class="settings-intro panel"><div><span class="settings-eyebrow">المتابعة</span><h1>سجل النشاط</h1><p>أحدث تغييرات الفواتير والعملاء والموظفين والأدوار في النظام.</p></div></div>
<section class="settings-section panel" style="padding:18px;margin-top:18px"><form method="get" action="{{ route('settings.activity') }}" class="activity-filters"><label>بحث<input name="q" value="{{ request('q') }}" placeholder="الحدث أو العنصر"></label><label>نوع الحدث<select name="type"><option value="">الجميع</option>@foreach(['invoice'=>'الفواتير','client'=>'العملاء','staff'=>'الموظفون','role'=>'الأدوار'] as $value=>$label)<option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
@endforeach
</select></label><label>الفترة<select name="period"><option value="">كل الفترات</option><option value="today" @selected(request('period') === 'today')>اليوم</option><option value="week" @selected(request('period') === 'week')>آخر 7 أيام</option><option value="month" @selected(request('period') === 'month')>آخر 30 يومًا</option></select></label><button class="btn gray" type="submit">بحث</button><a class="btn gray" href="{{ route('settings.activity') }}">مسح</a></form>
<div class="activity-list">@forelse($activities as $entry)<article class="activity-entry"><div class="activity-icon">✓</div><div><strong>{{ $entry->event }}</strong><p>{{ $entry->target }} — {{ $entry->summary }}</p><small>{{ $entry->actor?->name ?? 'النظام' }} · {{ $entry->created_at->format('d/m/Y H:i') }}</small></div></article>
@empty
<div class="settings-empty"><strong>لا توجد أحداث بعد</strong><p>ستظهر هنا العمليات بعد حفظها في قاعدة البيانات.</p></div>
@endforelse
</div></section><div style="margin-top:16px">{{ $activities->links() }}</div>
@endsection
