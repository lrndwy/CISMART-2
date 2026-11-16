<div class="bg-stone-50 py-14">
    <div class="mx-auto max-w-md px-4">
        <div class="rounded-2xl border border-stone-200/70 bg-white p-8 text-center shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-50">
                <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </span>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-stone-900">Verifikasi email dulu</h1>
            <p class="mt-2 text-sm leading-relaxed text-stone-600">
                Kami sudah mengirim tautan verifikasi ke
                <span class="font-medium text-stone-900">{{ auth()->user()?->email }}</span>.
                Klik tautan itu untuk membuka pesanan, checkout, dan profil.
            </p>
            <p class="mt-3 rounded-lg bg-stone-50 px-3 py-2 text-xs text-stone-500">
                Lingkungan lokal: email masuk ke <code>storage/logs/laravel.log</code> (MAIL_MAILER=log).
            </p>

            <div class="mt-6 space-y-2">
                <button wire:click="resend" wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-green-700 py-3 font-medium text-white transition hover:bg-green-800 active:scale-[0.98] disabled:opacity-60">
                    <span wire:loading.remove wire:target="resend">Kirim ulang email verifikasi</span>
                    <span wire:loading wire:target="resend">Mengirim...</span>
                </button>
                <button wire:click="logout"
                    class="w-full rounded-lg border border-stone-300 py-3 text-sm font-medium text-stone-700 transition hover:border-stone-400">
                    Keluar
                </button>
            </div>
        </div>
    </div>
</div>