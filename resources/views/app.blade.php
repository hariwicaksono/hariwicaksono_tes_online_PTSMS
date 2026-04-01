<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $settings['site_name'] ?? 'My App' }}</title>
  <link rel="icon" href="{{ $settings['site_logo'] }}" type="image/x-icon">
  @vite('resources/js/app.js')
  @php
  $manifestFile = public_path('/build/manifest.webmanifest');
  @endphp
  @if (file_exists($manifestFile))
  <link rel="manifest" href="/build/manifest.webmanifest">
  @endif
  <link rel="apple-touch-icon" href="/pwa/apple-touch-icon.png">
  <meta name="theme-color" content="#ffffff" />
</head>

<body>
  <div id="app"></div>
</body>

</html>