<!-- resources/views/components/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'CISMART - Pasar Online UMKM Cilacap' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Styles -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            @import 'tailwindcss';

            @source '../views';
            @source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';

            @theme {
                --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
            }
        </style>
    @endif

    @livewireStyles
</head>



<body class="antialiased bg-gray-50">
    <!-- Header Component -->
    <livewire:components.header />

    <!-- Main Content -->
    <main class="min-h-screen">
        {{ $slot }}
    </main>

    <!-- Footer Component -->
    <livewire:components.footer />

    <!-- Toast Notifications -->
    <div x-data="{
        show: false,
        message: '',
        type: 'success',
        init() {
            Livewire.on('notify', (data) => {
                this.message = data.message || data[0].message;
                this.type = data.type || data[0].type || 'success';
                this.show = true;
                setTimeout(() => { this.show = false }, 3000);
            });
        }
    }" x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed top-4 right-4 z-50 max-w-sm"
        style="display: none;">
        <div :class="{
            'bg-green-500': type === 'success',
            'bg-red-500': type === 'error',
            'bg-blue-500': type === 'info',
            'bg-yellow-500': type === 'warning'
        }"
            class="text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3">
            <span x-text="message"></span>
            <button @click="show = false" class="ml-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    {{-- @fluxScripts --}}
    @stack('scripts')

    <script>
        // Open URL in new tab
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-url', (event) => {
                window.open(event.url || event[0].url, '_blank');
            });
        });
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    {{-- Livewire sudah menyertakan Alpine; jangan load Alpine CDN lagi (wire:click akan mati). --}}
    @livewireScripts
</body>

</html>
