<div class="bg-stone-50 py-10 lg:py-16">
    <div class="max-w-5xl mx-auto px-4">
        <div class="grid lg:grid-cols-[1.05fr_1fr] overflow-hidden rounded-2xl border border-stone-200/70 bg-white shadow-[0_12px_28px_-14px_rgba(28,25,23,0.18)]">
            {{-- Brand panel --}}
            @include('livewire.pages.partials.auth-brand-panel')

            {{-- Form --}}
            <section class="p-8 sm:p-10">
                <img src="/logo-nobg.png" alt="CISMART" class="h-8 mb-6 lg:hidden">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Daftar</p>
                <h1 class="mt-2 text-2xl font-bold text-stone-900">Buat akun pembeli</h1>
                <p class="text-sm text-stone-500 mt-1 mb-8">Belanja produk UMKM Cilacap dan bayar dengan QRIS.</p>

                <form wire:submit="register" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" wire:model.blur="name" autocomplete="name"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                            placeholder="Nama Anda" />
                        @error('name')
                            <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                        <input type="email" wire:model.blur="email" autocomplete="username"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                            placeholder="anda@email.com" />
                        @error('email')
                            <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Nomor Telepon</label>
                        <input type="tel" wire:model.blur="phone" autocomplete="tel"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                            placeholder="0812xxxxxxxx" />
                        @error('phone')
                            <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Kata Sandi</label>
                        <input type="password" wire:model.blur="password" autocomplete="new-password"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                            placeholder="••••••••" />
                        <p class="text-xs text-stone-400 mt-1.5">Minimal 8 karakter.</p>
                        @error('password')
                            <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Konfirmasi Kata Sandi</label>
                        <input type="password" wire:model.blur="password_confirmation" autocomplete="new-password"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                            placeholder="••••••••" />
                        @error('password_confirmation')
                            <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="w-full bg-green-700 hover:bg-green-800 text-white rounded-lg py-3 font-medium active:scale-[0.98] transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="register">Daftar</span>
                        <span wire:loading wire:target="register">Memproses…</span>
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-stone-200">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm text-stone-500">Sudah punya akun?</span>
                        <a href="{{ route('login') }}" class="text-sm font-medium text-green-700 hover:text-green-800">Masuk</a>
                    </div>
                    <a href="{{ route('register.umkm') }}" class="text-sm text-stone-500 hover:text-green-700 mt-1 block">
                        Daftar sebagai UMKM
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
