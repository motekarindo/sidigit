<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Invoice')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        @page {
            size: A5 landscape;
            margin: 6mm;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
            }
        }
        .invoice-page {
            max-width: 210mm;
            font-size: 11px;
            line-height: 1.3;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 text-gray-900 antialiased">
    <main class="invoice-page mx-auto w-full p-4 md:p-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
