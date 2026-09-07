<div class="min-h-[70vh] bg-stone-50 py-12">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white border border-stone-200 shadow-sm p-8">
            <p class="text-xs tracking-[0.25em] uppercase text-green-700 font-semibold mb-2">CISMART</p>
            <h1 class="text-2xl font-bold text-stone-900 mb-1">Masuk</h1>
            <p class="text-sm text-stone-600 mb-8">Gunakan akun pembeli atau penjual untuk checkout dan pembayaran QRIS.</p>

            <form wire:submit="login" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
                    <input type="email" wire:model.live="email" autocomplete="username"
                        class="w-full px-3 py-2 border border-stone-300 rounded-none focus:ring-2 focus:ring-green-600 focus:border-transparent"
                        placeholder="buyer@cismart.test" />
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Kata sandi</label>
                    <input type="password" wire:model.live="password" autocomplete="current-password"
                        class="w-full px-3 py-2 border border-stone-300 rounded-none focus:ring-2 focus:ring-green-600 focus:border-transparent" />
                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="w-full bg-green-700 text-white py-3 font-medium hover:bg-green-800 transition-colors">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>
