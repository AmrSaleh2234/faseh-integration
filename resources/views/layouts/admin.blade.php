<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Titanic ways')</title>
    @stack('styles')
</head>
<body>
    <div class="app">
        @include('layouts.partials.sidebar')

        <div class="workspace">
            @include('layouts.partials.topbar')
            @yield('page-actions')

            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>

    <button class="chat" type="button">✦ لديك سؤال</button>
    @stack('dialogs')
    <script src="{{ asset('sidebar.js') }}"></script>
    @stack('scripts')
</body>
</html>
