{{--
    Booking module standalone layout.
    Minimal HTML shell used for module-specific pages that don't inherit the
    Theme module's layout (e.g. isolated previews, widgets). Component-style
    usage: <x-booking::layouts.master>...</x-booking::layouts.master>.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        {{-- Standard meta tags: charset, viewport, CSRF token, IE compat. --}}
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <title>Booking Module - {{ config('app.name', 'Laravel') }}</title>

        {{-- SEO meta — driven by variables passed through the component slot. --}}
        <meta name="description" content="{{ $description ?? '' }}">
        <meta name="keywords" content="{{ $keywords ?? '' }}">
        <meta name="author" content="{{ $author ?? '' }}">

        {{-- Fonts: preload Bunny CDN then load Figtree for the UI typography. --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- Module-specific Vite CSS (commented until assets are authored). --}}
        {{-- {{ module_vite('build-booking', 'resources/assets/sass/app.scss') }} --}}
    </head>

    <body>
        {{-- Slot: the actual page content is injected here. --}}
        {{ $slot }}

        {{-- Module-specific Vite JS (commented until assets are authored). --}}
        {{-- {{ module_vite('build-booking', 'resources/assets/js/app.js') }} --}}
    </body>
</html>
