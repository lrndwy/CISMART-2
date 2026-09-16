@if ($application && $application->status !== 'rejected')
    {{-- Status: pending / approved --}}
    <div class="bg-stone-50 py-12">
        <div class="max-w-2xl mx-auto px-4">
            <div class="rounded-2xl border border-stone-200/70 bg-white p-8 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                @if ($application->status === 'pending')
                    <div class="flex items-start gap-4">
                        <span class="bg-amber-50 text-amber-600 rounded-lg p-2.5 h-fit">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Permohonan sedang ditinjau</h1>
                            <p class="text-stone-600 leading-relaxed mt-2">
                                Toko {{ $application->business_name }} sudah kami terima. Tim CISMART meninjau permohonan
                                maksimal 1x24 jam. Kamu tetap bisa belanja sebagai pembeli sambil menunggu.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex items-start gap-4">
                        <span class="bg-green-50 text-green-700 rounded-lg p-2.5 h-fit">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-stone-900">Akun UMKM disetujui</h1>
                            <p class="text-stone-600 leading-relaxed mt-2">
                                Selamat, toko {{ $application->business_name }} sudah aktif. Dashboard penjual membuka
                                pengelolaan toko dan produk kamu.
                            </p>
                        </div>
                    </div>
                @endif

                <dl class="mt-6 divide-y divide-stone-200 border-t border-stone-200 text-sm">
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-stone-500">Nama Toko</dt>
                        <dd class="text-stone-900 font-medium text-right">{{ $application->business_name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-stone-500">Jenis Usaha</dt>
                        <dd class="text-stone-900 font-medium text-right">{{ $application->business_type }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-stone-500">Diajukan</dt>
                        <dd class="text-stone-900 font-medium text-right">{{ $application->created_at->translatedFormat('d F Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-stone-500">Status</dt>
                        <dd class="text-right">
                            @if ($application->status === 'pending')
                                <span class="bg-amber-50 text-amber-700 rounded-full px-2 py-1 text-xs font-medium">Menunggu peninjauan</span>
                            @else
                                <span class="bg-green-50 text-green-700 rounded-full px-2 py-1 text-xs font-medium">Disetujui</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if (! empty($application->business_documents))
                    <div class="mt-6">
                        <p class="text-sm font-medium text-stone-700 mb-2">Dokumen pendukung</p>
                        <ul class="divide-y divide-stone-200 border border-stone-200/70 rounded-xl text-sm">
                            @foreach ($application->business_documents as $path)
                                <li class="px-4 py-2.5">
                                    <a href="{{ Storage::disk('public')->url($path) }}" target="_blank"
                                        class="text-green-700 hover:text-green-800 font-medium">{{ basename($path) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    @if ($application->status === 'approved')
                        <a href="{{ url('/admin') }}"
                            class="bg-green-700 text-white rounded-lg px-5 py-2.5 text-sm font-medium hover:bg-green-800 active:scale-[0.98] transition">
                            Buka Dashboard Penjual
                        </a>
                        <a href="{{ route('shops.index') }}" class="text-sm text-stone-500 hover:text-green-700">
                            Lihat toko di CISMART
                        </a>
                    @else
                        <a href="{{ route('products.index') }}"
                            class="bg-green-700 text-white rounded-lg px-5 py-2.5 text-sm font-medium hover:bg-green-800 active:scale-[0.98] transition">
                            Lanjut belanja
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Form pengajuan / pengajuan ulang --}}
    <div class="bg-stone-50 py-10 lg:py-16">
        <div class="max-w-5xl mx-auto px-4">
            <div class="grid lg:grid-cols-[1.05fr_1fr] overflow-hidden rounded-2xl border border-stone-200/70 bg-white shadow-[0_12px_28px_-14px_rgba(28,25,23,0.18)]">
                {{-- Brand panel --}}
                @include('livewire.pages.partials.auth-brand-panel')

                {{-- Form --}}
                <section class="p-8 sm:p-10">
                    <img src="/logo-nobg.png" alt="CISMART" class="h-8 mb-6 lg:hidden">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Daftar UMKM</p>
                    <h1 class="mt-2 text-2xl font-bold text-stone-900">Titip jual produkmu di CISMART</h1>
                    <p class="text-sm text-stone-500 mt-1 mb-8">Isi data toko. Akun dan toko ditinjau admin sebelum aktif.</p>

                    @if ($application?->status === 'rejected')
                        <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl p-4 mb-6">
                            <p class="font-semibold">Permohonan sebelumnya ditolak</p>
                            @if ($application->rejection_reason)
                                <p class="mt-1">{{ $application->rejection_reason }}</p>
                            @endif
                        </div>
                    @endif

                    <form wire:submit="submit" class="space-y-4">
                        @guest
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
                        @endguest

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1.5">Nama Toko</label>
                            <input type="text" wire:model.blur="business_name"
                                class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                                placeholder="Contoh: Batik Cilacap Berkah" />
                            @error('business_name')
                                <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1.5">Jenis Usaha</label>
                            <x-select-dropdown prop="business_type" class="w-full"
                                :options="[
                                    ['value' => '', 'label' => 'Pilih jenis usaha…'],
                                    ['value' => 'food', 'label' => 'Makanan & Minuman'],
                                    ['value' => 'craft', 'label' => 'Kerajinan Tangan'],
                                    ['value' => 'souvenir', 'label' => 'Souvenir'],
                                    ['value' => 'fashion', 'label' => 'Fashion'],
                                    ['value' => 'agriculture', 'label' => 'Pertanian & Perkebunan'],
                                    ['value' => 'technology', 'label' => 'Teknologi'],
                                    ['value' => 'service', 'label' => 'Jasa'],
                                    ['value' => 'other', 'label' => 'Lainnya'],
                                ]" />
                            @error('business_type')
                                <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1.5">Deskripsi Toko</label>
                            <textarea wire:model.blur="business_description" rows="4"
                                class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                                placeholder="Ceritakan produk dan keunggulan tokomu."></textarea>
                            @error('business_description')
                                <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1.5">Alamat Toko</label>
                            <textarea wire:model.blur="business_address" rows="3"
                                class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                                placeholder="Alamat lengkap toko/produksi."></textarea>
                            @error('business_address')
                                <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-stone-700 mb-1.5">No HP Toko</label>
                                <input type="tel" wire:model.blur="business_phone"
                                    class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                                    placeholder="0812xxxxxxxx" />
                                @error('business_phone')
                                    <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-stone-700 mb-1.5">Email Toko</label>
                                <input type="email" wire:model.blur="business_email"
                                    class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                                    placeholder="toko@email.com" />
                                @error('business_email')
                                    <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1.5">Dokumen Pendukung (KTP/SIUP)</label>
                            <input type="file" wire:model="documents" multiple accept="image/*,application/pdf"
                                class="block w-full text-sm text-stone-600 rounded-lg border border-stone-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:bg-green-700 file:text-white file:text-sm file:font-medium hover:file:bg-green-800" />
                            <p class="text-xs text-stone-400 mt-1.5">Unggah 1-3 file (JPG, PNG, atau PDF, maksimal 2 MB per file).</p>
                            <div wire:loading wire:target="documents" class="text-sm text-stone-500 mt-1.5">Mengunggah…</div>

                            @if (! empty($documents))
                                <ul class="divide-y divide-stone-200 border border-stone-200/70 rounded-xl text-sm mt-2">
                                    @foreach ($documents as $doc)
                                        <li class="px-4 py-2.5 text-stone-700">{{ $doc->getClientOriginalName() }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($this->hasStoredDocuments())
                                <p class="text-sm font-medium text-stone-700 mt-3 mb-1.5">Dokumen sudah terunggah</p>
                                <ul class="divide-y divide-stone-200 border border-stone-200/70 rounded-xl text-sm">
                                    @foreach ($application->business_documents as $path)
                                        <li class="px-4 py-2.5">
                                            <a href="{{ Storage::disk('public')->url($path) }}" target="_blank"
                                                class="text-green-700 hover:text-green-800 font-medium">{{ basename($path) }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @error('documents')
                                <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                            @error('documents.*')
                                <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <p class="text-xs text-stone-500 bg-stone-50 border border-stone-200/70 rounded-lg p-3">
                            Akun UMKM aktif setelah disetujui admin (maksimal 1x24 jam). Sebelum itu kamu tetap bisa
                            belanja sebagai pembeli.
                        </p>

                        <button type="submit"
                            class="w-full bg-green-700 hover:bg-green-800 text-white rounded-lg py-3 font-medium active:scale-[0.98] transition disabled:opacity-60">
                            <span wire:loading.remove wire:target="submit">{{ $application?->status === 'rejected' ? 'Ajukan Ulang' : 'Kirim Permohonan' }}</span>
                            <span wire:loading wire:target="submit">Mengirim…</span>
                        </button>
                    </form>

                    <div class="mt-6 pt-6 border-t border-stone-200">
                        @guest
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm text-stone-500">Sudah punya akun?</span>
                                <a href="{{ route('login') }}" class="text-sm font-medium text-green-700 hover:text-green-800">Masuk</a>
                            </div>
                            <a href="{{ route('register') }}" class="text-sm text-stone-500 hover:text-green-700 mt-1 block">
                                Daftar sebagai pembeli
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="text-sm text-stone-500 hover:text-green-700">
                                Daftar sebagai pembeli
                            </a>
                        @endguest
                    </div>
                </section>
            </div>
        </div>
    </div>
@endif
