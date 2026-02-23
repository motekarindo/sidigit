<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $pageTitleSection = $__env->yieldContent('title');
        $pageTitle = $title ?? ($pageTitleSection !== '' ? $pageTitleSection : null);
    @endphp
    <title>{{ $pageTitle ?? config('app.name', 'TailAdmin') }}</title>

    <script>
        (function() {
            try {
                var isDark = localStorage.getItem('darkMode') === 'true';
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-theme-preload', '');
                }
                document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            } catch (e) {}
        })();
    </script>
    <style>
        [data-theme-preload] *,
        [data-theme-preload] *::before,
        [data-theme-preload] *::after {
            transition: none !important;
        }
        [data-theme="dark"][data-theme-preload] body {
            background-color: #111827;
        }
        [data-theme="dark"][data-theme-preload] .bg-white,
        [data-theme="dark"][data-theme-preload] .bg-gray-50 {
            background-color: #111827 !important;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.deferLoadingAlpine = function(callback) {
            window.addEventListener('DOMContentLoaded', () => callback());
        };
    </script>
    @livewireStyles
    @stack('styles')
</head>

@php
    $currentRoute = Route::currentRouteName();
@endphp

<body x-data="{
    page: @js($currentRoute),
    loaded: true,
    darkMode: JSON.parse(localStorage.getItem('darkMode') ?? 'false'),
    stickyMenu: false,
    sidebarToggle: false,
    scrollTop: false,
}" x-init="$watch('darkMode', value => { document.documentElement.setAttribute('data-theme-preload', ''); localStorage.setItem('darkMode', JSON.stringify(value)); document.documentElement.classList.toggle('dark', value); document.documentElement.style.colorScheme = value ? 'dark' : 'light'; document.documentElement.setAttribute('data-theme', value ? 'dark' : 'light'); window.setTimeout(() => document.documentElement.removeAttribute('data-theme-preload'), 80); })" :class="{ 'dark bg-gray-900': darkMode === true }"
    class="text-gray-700 antialiased">

    <livewire:layout.preloader />

    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
        <livewire:layout.sidebar />

        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
            <livewire:layout.overlay />
            <livewire:layout.header />

            <main class="flex-1">
                <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
            </main>

            <livewire:layout.footer />
        </div>
    </div>

    <x-toast />

    @if (session('toast'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: @js(session('toast')),
                }));
            });
        </script>
    @endif
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            document.documentElement.removeAttribute('data-theme-preload');
        });
    </script>
    <script>
        (function() {
            function registerRupiahField() {
                if (!window.Alpine || window.__rupiahFieldRegistered) return;
                window.__rupiahFieldRegistered = true;
                Alpine.data('rupiahField', (model) => ({
                    value: model,
                    display: '',
                    init() {
                        this.display = this.format(this.value);
                        this.$watch('value', (val) => {
                            this.display = this.format(val);
                        });
                    },
                    onInput() {
                        const raw = (this.display || '').replace(/[^\d]/g, '');
                        this.value = raw ? parseInt(raw, 10) : null;
                        this.display = raw ? this.format(raw) : '';
                    },
                    format(val) {
                        if (val === null || val === undefined || val === '') return '';
                        const num = typeof val === 'string' ? parseInt(val, 10) : Number(val);
                        if (!Number.isFinite(num)) return '';
                        return new Intl.NumberFormat('id-ID').format(num);
                    },
                }));
            }

            document.addEventListener('alpine:init', registerRupiahField);
            if (window.Alpine) {
                registerRupiahField();
            }
        })();
    </script>

    @stack('scripts')
    @livewireScripts
</body>

</html>
