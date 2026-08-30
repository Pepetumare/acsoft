@php
    $destination = app(\App\Support\ErrorReturnDestination::class)->resolve(request(), $status);
@endphp
<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0F2744">
    <title>{{ $status }} | ACSoft</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/acsoft/favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .error-page { min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; background: radial-gradient(circle at 85% 15%, rgba(36, 151, 227, .14), transparent 32%), #f7f9fc; }
        .error-card { width: min(100%, 680px); padding: clamp(2rem, 6vw, 4rem); border: 1px solid #e3e8ef; border-radius: 24px; background: #fff; box-shadow: 0 24px 70px rgba(15, 39, 68, .12); text-align: center; }
        .error-logo { width: min(190px, 55vw); height: auto; margin-bottom: 2rem; }
        .error-code { color: #2497e3; font-size: clamp(4.5rem, 16vw, 8rem); font-weight: 800; letter-spacing: -.08em; line-height: .85; }
        .error-title { margin: 1.5rem 0 .75rem; color: #0f2744; font-size: clamp(1.6rem, 5vw, 2.35rem); font-weight: 750; }
        .error-message { max-width: 500px; margin: 0 auto 2rem; color: #657084; font-size: 1.05rem; line-height: 1.65; }
        .error-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: .75rem; }
        @media (max-width: 480px) { .error-actions > * { width: 100%; } }
    </style>
</head>
<body>
    <main class="error-page">
        <section class="error-card" aria-labelledby="error-title">
            <img src="{{ asset('images/acsoft/logo.svg') }}" alt="ACSoft" class="error-logo">
            <div class="error-code" aria-hidden="true">{{ $status }}</div>
            <h1 id="error-title" class="error-title">{{ $title }}</h1>
            <p class="error-message">{{ $message }}</p>
            <div class="error-actions">
                <a href="{{ $destination['url'] }}" class="btn btn-acsoft-primary px-4 py-2">
                    {{ $destination['label'] }}
                </a>
                @if ($destination['secondary_url'])
                    <a href="{{ $destination['secondary_url'] }}" class="btn btn-acsoft-outline px-4 py-2">
                        {{ $destination['secondary_label'] }}
                    </a>
                @endif
            </div>
        </section>
    </main>
</body>
</html>
