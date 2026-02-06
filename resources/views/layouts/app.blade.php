<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
<head>
    <base href="{{ asset('') }}"/>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>@yield('title', 'Dashboard') - PlutoPay</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="assets/vendors/apexcharts/apexcharts.css" rel="stylesheet"/>
    <link href="assets/vendors/keenicons/styles.bundle.css" rel="stylesheet"/>
    <link href="assets/css/styles.css" rel="stylesheet"/>
    @stack('styles')
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background demo1 kt-sidebar-fixed kt-header-fixed">
    <script>
        const defaultThemeMode = 'light';
        let themeMode;
        if (document.documentElement) {
            if (localStorage.getItem('kt-theme')) {
                themeMode = localStorage.getItem('kt-theme');
            } else if (document.documentElement.hasAttribute('data-kt-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-kt-theme-mode');
            } else {
                themeMode = defaultThemeMode;
            }
            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.classList.add(themeMode);
        }
    </script>
    <div class="flex grow">
        @include('layouts.partials.sidebar')
        <div class="kt-wrapper flex grow flex-col">
            @include('layouts.partials.header')
            <main class="grow pt-5" id="content" role="content">
                <div class="kt-container-fixed" id="contentContainer"></div>
                <div class="kt-container-fixed">
                    @yield('content')
                </div>
            </main>
            @include('layouts.partials.footer')
        </div>
    </div>
    <script src="assets/js/core.bundle.js"></script>
    <script src="assets/vendors/ktui/ktui.min.js"></script>
    <script src="assets/vendors/apexcharts/apexcharts.min.js"></script>
    <script src="assets/js/layouts/demo1.js"></script>
    @stack('scripts')
</body>
</html>
