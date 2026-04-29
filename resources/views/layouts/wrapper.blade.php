<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @class (['dark' => ($appearance ?? 'system') === 'dark'])
>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>
        {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function () {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia(
                    '(prefers-color-scheme: dark)',
                ).matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    <link rel="icon" href="/favicon.ico" sizes="any" />
    <link rel="icon" href="/logo.svg" type="image/svg+xml" />
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="msapplication-TileColor" content="{{ \Filament\Support\Colors\Color::convertToHex(config('branding.color.secondary.500')) }}">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <meta name="theme-color" content="{{ \Filament\Support\Colors\Color::convertToHex(config('branding.color.primary.500')) }}">

    <style>
        :root {
            @foreach (config('branding.color') as $color => $thing)
                @foreach ($thing as $shade => $value)
                    --color-{{ $color }}-{{ $shade }}: {{ $value }};
                @endforeach
            @endforeach
        }
    </style>

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link
        href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
        rel="stylesheet"
    />

    @vite (['resources/css/app.css', 'resources/js/app.ts'])
    @fluxAppearance
</head>
<body {{ $attributes->except('title') }}>
    {{ $slot }}

    @fluxScripts
</body>
</html>
