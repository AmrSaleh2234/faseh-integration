<header class="topbar">
    <div class="breadcrumb">
        <button id="sidebar-toggle" class="mobile-toggle" type="button">☰</button>
        <span class="menu-glyph">☷</span>
        <span>@yield('section-name', 'الفواتير')</span>
        <span>‹</span>
        <span>@yield('page-name', 'إضافة')</span>
    </div>

    <div class="account">
        <span>المساعدة ◉</span>
        <span>🔔</span>
        <span>{{ auth()->user()->name }}<br><small>{{ now()->format('d/m/Y H:i') }}</small></span>
        <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="topbar-logout">خروج</button></form>
    </div>
</header>
