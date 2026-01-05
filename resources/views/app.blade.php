<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#1e3a8a">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="SGAF">
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        
        <!-- Apple Touch Icons -->
        <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
        <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('icons/icon-512x512.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
        
        <!-- Service Worker Registration -->
        <!-- DISABLED: Causing performance issues -->
        <!--
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('{{ asset('service-worker.js') }}')
                        .then(registration => {
                            console.log('ServiceWorker registered: ', registration);
                        })
                        .catch(error => {
                            console.log('ServiceWorker registration failed: ', error);
                        });
                });
            }
        </script>
        -->
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
