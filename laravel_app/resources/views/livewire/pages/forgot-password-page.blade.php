<div class="bg-stone-50 py-14">
    <div class="mx-auto max-w-md px-4">
        <div class="rounded-2xl border border-stone-200/70 bg-white p-8 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Lupa kata sandi</h1>
            <p class="mt-2 text-sm leading-relaxed text-stone-600">
                Masukkan email akunmu. Kami kirim tautan untuk mengatur ulang kata sandi.
            </p>

            @if ($sent)
                <div class="mt-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">
                    Bila email tersebut terdaftar, tautan reset sudah dikirim. Cek kotak masuk atau folder spam.
                    <span class="mt-2 block text-xs text-green-700">
                        Di lingkungan lokal, email masuk ke <code>storage/logs/laravel.log</code> (MAIL_MAILER=log).
                    </span>
                </div>
            @else
                <form wire:submit="sendResetLink" class="mt-6 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-stone-700">Email</label>
                        <input type="email" wire:model.blur="email"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:border-transparent focus:ring-2 focus:ring-green-700 focus:outline-none"
                            placeholder="nama@email.com" />
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full rounded-lg bg-green-700 py-3 font-medium text-white transition hover:bg-green-800 active:scale-[0.98] disabled:opacity-60">
                        <span wire:loading.remove wire:target="sendResetLink">Kirim tautan reset</span>
                        <span wire:loading wire:target="sendResetLink">Mengirim...</span>
                    </button>
                </form>
            @endif

            <a href="{{ route('login') }}" class="mt-6 block text-sm font-medium text-green-700 hover:text-green-800">
                Kembali ke halaman masuk
            </a>
        </div>
    </div>
</div>
