<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'POS System - Raabiha' }}</title>
    
    @php
        $faviconId = \App\Models\SiteSetting::where('key', 'site_favicon')->value('value');
        $faviconMedia = $faviconId ? \Awcodes\Curator\Models\Media::find($faviconId) : null;
        $faviconUrl = $faviconMedia ? \Illuminate\Support\Facades\Storage::url($faviconMedia->path) : asset('favicon.ico');

        $faviconMime = 'image/x-icon';
        if (\Illuminate\Support\Str::endsWith(strtolower($faviconUrl), '.png')) $faviconMime = 'image/png';
        elseif (\Illuminate\Support\Str::endsWith(strtolower($faviconUrl), '.jpg') || \Illuminate\Support\Str::endsWith(strtolower($faviconUrl), '.jpeg')) $faviconMime = 'image/jpeg';
        elseif (\Illuminate\Support\Str::endsWith(strtolower($faviconUrl), '.svg')) $faviconMime = 'image/svg+xml';
    @endphp

    <link rel="icon" type="{{ $faviconMime }}" href="{{ $faviconUrl }}" />
    <link rel="shortcut icon" type="{{ $faviconMime }}" href="{{ $faviconUrl }}" />
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Hide Alpine elements before initialization */
        [x-cloak] { display: none !important; }

        /* Smooth scrolling */
        html { scroll-behavior: smooth; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Raabiha POS">

    @livewireStyles
    @livewireScriptConfig
    @livewireScripts
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased overflow-hidden selection:bg-brand-500 selection:text-white">
    
    {{ $slot }}

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW Reg Failed:', err));
            });
        }
    </script>
</body>
</html>
