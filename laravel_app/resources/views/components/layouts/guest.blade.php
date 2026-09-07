<!-- resources/views/components/layouts/guest.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'CISMART' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="mb-6">
            <a href="/">
                <div class="flex items-center">
                    <div class="bg-green-600 text-white rounded-lg p-3 mr-3">
                        <div class="font-bold text-2xl">CI</div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">CISMART</h1>
                        <p class="text-sm text-gray-500">Pasar Online Cilacap</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

    @fluxScripts
    @livewireScripts
</body>

</html>
