<!DOCTYPE html>
<html lang="es-CL">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'ACSoft | Software simple para negocios reales')
    </title>

    <meta name="description" content="@yield('description', 'ACSoft desarrolla sistemas web simples para digitalizar y ordenar procesos de pequeños negocios.')">

    <meta name="robots" content="@yield('robots', 'index, follow')">

    <link rel="canonical" href="@yield('canonical', url()->current())">


    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/acsoft/favicon.svg') }}">

    <link rel="apple-touch-icon" href="{{ asset('images/acsoft/apple-touch-icon.png') }}">

    <link rel="manifest" href="{{ route('pwa.manifest') }}">


    {{-- Open Graph --}}
    <meta property="og:type" content="website">

    <meta property="og:site_name" content="ACSoft">

    <meta property="og:title" content="@yield('og_title', 'ACSoft | Software simple para negocios reales')">

    <meta property="og:description" content="@yield('og_description', 'Sistemas web simples y adaptados a pequeños negocios.')">

    <meta property="og:url" content="@yield('canonical', url()->current())">

    <meta property="og:image" content="@yield('og_image', asset('images/acsoft/og-acsoft.jpg'))">


    {{-- Twitter / X --}}
    <meta name="twitter:card" content="summary_large_image">

    <meta name="twitter:title" content="@yield('og_title', 'ACSoft | Software simple para negocios reales')">

    <meta name="twitter:description" content="@yield('og_description', 'Sistemas web simples y adaptados a pequeños negocios.')">

    <meta name="twitter:image" content="@yield('og_image', asset('images/acsoft/og-acsoft.jpg'))">


    {{-- Color navegador móvil --}}
    <meta name="theme-color" content="#0F2744">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>

<body>
    <a href="#main-content" class="skip-link">
        Saltar al contenido
    </a>
    <x-navbar />

    <main id="main-content">
        @yield('content')
    </main>

    <x-footer />
    @stack('scripts')
</body>

</html>
