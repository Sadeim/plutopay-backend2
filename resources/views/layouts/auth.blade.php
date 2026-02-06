<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
<head>
    <base href="{{ asset('') }}"/>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>@yield('title', 'Login') - PlutoPay</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="assets/vendors/apexcharts/apexcharts.css" rel="stylesheet"/>
    <link href="assets/vendors/keenicons/styles.bundle.css" rel="stylesheet"/>
    <link href="assets/css/styles.css" rel="stylesheet"/>
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
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

    <style>
        .page-bg { background-image: url('assets/media/images/2600x1200/bg-10.png'); }
        .dark .page-bg { background-image: url('assets/media/images/2600x1200/bg-10-dark.png'); }
    </style>

    <div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
        <div class="kt-card max-w-[370px] w-full">
            <div class="kt-card-content flex flex-col gap-5 p-10">
                <div class="flex justify-center mb-2">
                    <span class="text-2xl font-bold text-primary">PlutoPay</span>
                </div>
                @yield('content')
            </div>
        </div>
    </div>

    <script src="assets/js/core.bundle.js"></script>
    <script src="assets/vendors/ktui/ktui.min.js"></script>
    <script src="assets/vendors/apexcharts/apexcharts.min.js"></script>
</body>
</html>
