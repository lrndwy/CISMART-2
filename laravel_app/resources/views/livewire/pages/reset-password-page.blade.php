<div class="bg-stone-50 py-14">
    <div class="mx-auto max-w-md px-4">
        <div class="rounded-2xl border border-stone-200/70 bg-white p-8 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Atur kata sandi baru</h1>
            <p class="mt-2 text-sm text-stone-600">Masukkan kata sandi baru untuk akunmu.</p>

            <form wire:submit="resetPassword" class="mt-6 space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-stone-700">Email</label>
                    <input type="email" wire:model.blur="email"
                        class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 focus:border-transparent focus:ring-2 focus:ring-green-700 focus:outline-none" />
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-stone-700">Kata sandi baru</label>
                    <input type="password" wire:model.blur="password" autocomplete="new-password"
                        class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 focus:border-transparent focus:ring-2 focus:ring-green-700 focus:outline-none"
                        placeholder="Minimal 8 karakter" />
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-stone-700">Ulangi kata sandi</label>
                    <input type="password" wire:model.blur="password_confirmation" autocomplete="new-password"
                        class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 focus:border-transparent focus:ring-2 focus:ring-green-700 focus:outline-none" />
                </div>
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-green-700 py-3 font-medium text-white transition hover:bg-green-800 active:scale-[0.98] disabled:opacity-60">
                    <span wire:loading.remove wire:target="resetPassword">Simpan kata sandi</span>
                    <span wire:loading wire:target="resetPassword">Menyimpan...</span>
                </button>
            </form>
        </div>
    </div>
</div>
