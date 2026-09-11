@props([
    'appName' => 'Pemuda Cirengit',
])

<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#047857">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $appName ?: 'Pemuda Cirengit' }}">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/icon-512.png') }}">
