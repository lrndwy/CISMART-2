@php
    $version = config('legal.terms.version');
    $effectiveAt = \Illuminate\Support\Carbon::parse(config('legal.terms.effective_at'));
@endphp

<x-layouts.app>
    <div class="bg-stone-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 py-14 sm:py-20">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Dokumen Legal</p>
            <h1 class="mt-2 text-3xl sm:text-4xl font-bold tracking-tight text-stone-900">Syarat &amp; Ketentuan</h1>
            <p class="mt-3 text-sm text-stone-500">
                Versi {{ $version }} &middot; berlaku {{ $effectiveAt->translatedFormat('j F Y') }}
            </p>

            <p class="mt-6 text-stone-600 leading-relaxed">
                Syarat &amp; Ketentuan ini mengatur penggunaan CISMART, pasar online yang menghubungkan UMKM
                Cilacap dengan pembeli. Dengan membuat akun, berbelanja, atau berjualan di CISMART, kamu
                menyatakan telah membaca dan menyetujui dokumen ini.
            </p>

            {{-- Daftar isi --}}
            <nav aria-label="Daftar isi" class="mt-8 rounded-2xl border border-stone-200 bg-white p-5">
                <p class="text-sm font-semibold text-stone-900 mb-3">Daftar isi</p>
                <ol class="grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm text-green-700">
                    <li><a href="#definisi" class="hover:text-green-800 hover:underline">1. Definisi</a></li>
                    <li><a href="#akun" class="hover:text-green-800 hover:underline">2. Akun pengguna</a></li>
                    <li><a href="#kewajiban-penjual" class="hover:text-green-800 hover:underline">3. Kewajiban penjual</a></li>
                    <li><a href="#kewajiban-pembeli" class="hover:text-green-800 hover:underline">4. Kewajiban pembeli</a></li>
                    <li><a href="#transaksi" class="hover:text-green-800 hover:underline">5. Transaksi &amp; pembayaran</a></li>
                    <li><a href="#larangan" class="hover:text-green-800 hover:underline">6. Barang yang dilarang</a></li>
                    <li><a href="#sanksi" class="hover:text-green-800 hover:underline">7. Sanksi &amp; penutupan toko</a></li>
                    <li><a href="#hukum" class="hover:text-green-800 hover:underline">8. Hukum yang berlaku</a></li>
                </ol>
            </nav>

            <div class="mt-10 space-y-10 text-stone-700 leading-relaxed">
                <section id="definisi" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">1. Definisi</h2>
                    <ul class="mt-3 space-y-2 list-disc pl-5">
                        <li><strong>CISMART</strong> adalah platform pasar online untuk UMKM Cilacap, dikelola oleh pengelola CISMART.</li>
                        <li><strong>Penjual</strong> adalah pemilik usaha yang akunnya telah disetujui untuk membuka toko di CISMART.</li>
                        <li><strong>Pembeli</strong> adalah pengguna yang memesan produk melalui CISMART.</li>
                        <li><strong>Pesanan</strong> adalah transaksi antara pembeli dan penjual atas satu atau beberapa produk.</li>
                    </ul>
                </section>

                <section id="akun" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">2. Akun pengguna</h2>
                    <p class="mt-3">
                        Satu akun digunakan untuk satu orang atau satu usaha. Kamu wajib memberikan data yang benar,
                        menjaga kerahasiaan kata sandi, dan segera memberi tahu CISMART bila akunmu dipakai tanpa
                        izin. Aktivitas yang terjadi melalui akunmu dianggap dilakukan olehmu.
                    </p>
                    <p class="mt-3">
                        CISMART dapat meminta verifikasi email dan menonaktifkan akun yang datanya tidak dapat
                        diverifikasi.
                    </p>
                </section>

                <section id="kewajiban-penjual" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">3. Kewajiban penjual</h2>
                    <ul class="mt-3 space-y-2 list-disc pl-5">
                        <li>Mengunggah dokumen izin usaha (NIB) dan dokumen pendukung yang sah, serta menjaganya tetap berlaku.</li>
                        <li>Menjual produk yang halal, aman, dan sesuai deskripsi, foto, harga, serta stok yang ditampilkan.</li>
                        <li>Memproses pesanan dalam batas waktu yang ditampilkan di dashboard penjual.</li>
                        <li>Menanggapi pertanyaan dan keluhan pembeli dengan wajar.</li>
                        <li>Tidak memindahkan transaksi di luar CISMART untuk menghindari kewajiban yang berlaku di platform.</li>
                    </ul>
                    <p class="mt-3">
                        Rincian kewajiban, termasuk ketentuan biaya layanan, diatur dalam
                        <a href="{{ route('legal.seller-agreement') }}" target="_blank" rel="noopener noreferrer"
                            class="text-green-700 font-medium hover:text-green-800 hover:underline">Perjanjian Penjual</a>.
                    </p>
                </section>

                <section id="kewajiban-pembeli" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">4. Kewajiban pembeli</h2>
                    <ul class="mt-3 space-y-2 list-disc pl-5">
                        <li>Memberikan alamat pengiriman dan nomor kontak yang benar.</li>
                        <li>Menyelesaikan pembayaran sesuai metode yang dipilih sebelum batas waktu pesanan berakhir.</li>
                        <li>Meninjau produk secara jujur dan tidak menulis ulasan yang menyesatkan.</li>
                        <li>Tidak menyalahgunakan fitur ulasan, promo, atau pembatalan pesanan.</li>
                    </ul>
                </section>

                <section id="transaksi" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">5. Transaksi &amp; pembayaran</h2>
                    <p class="mt-3">
                        CISMART mempertemukan penjual dan pembeli; perjanjian jual beli lahir langsung antara keduanya.
                        Pembayaran diproses melalui penyedia pembayaran QRIS yang ditampilkan saat checkout. Pesanan
                        yang belum dibayar dalam batas waktu akan dibatalkan otomatis.
                    </p>
                    <p class="mt-3">
                        Ongkos kirim dihitung dari jarak antara toko dan alamat pembeli. Biaya layanan, bila diberlakukan,
                        akan diinformasikan sebelum pesanan dibuat dan menjadi bagian dari versi dokumen ini.
                    </p>
                </section>

                <section id="larangan" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">6. Barang yang dilarang</h2>
                    <p class="mt-3">Dilarang memperjualbelikan barang dan jasa yang:</p>
                    <ul class="mt-3 space-y-2 list-disc pl-5">
                        <li>melanggar hukum Indonesia, termasuk obat terlarang, senjata, dan barang palsu;</li>
                        <li>mengandung bahan berbahaya tanpa izin edar;</li>
                        <li>melanggar hak kekayaan intelektual pihak lain;</li>
                        <li>merupakan hewan atau tumbuhan yang dilindungi;</li>
                        <li>memuat konten dewasa, perjudian, atau jasa keuangan tanpa izin.</li>
                    </ul>
                </section>

                <section id="sanksi" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">7. Sanksi &amp; penutupan toko</h2>
                    <p class="mt-3">
                        Pelanggaran terhadap dokumen ini dapat berakibat pada penyembunyian produk, pembatalan pesanan,
                        penangguhan toko, hingga penutupan akun. Sanksi ditentukan dengan mempertimbangkan jenis,
                        dampak, dan apakah pelanggaran terulang.
                    </p>
                    <p class="mt-3">
                        Penjual atau pembeli yang dirugikan dapat mengajukan keberatan melalui kanal kontak CISMART
                        selambat-lambatnya 14 hari setelah kejadian.
                    </p>
                </section>

                <section id="hukum" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">8. Hukum yang berlaku</h2>
                    <p class="mt-3">
                        Dokumen ini diatur oleh hukum Republik Indonesia. Perselisihan diselesaikan terlebih dahulu
                        secara musyawarah; bila tidak tercapai, diselesaikan melalui pengadilan yang berwenang di
                        wilayah Cilacap, Jawa Tengah.
                    </p>
                    <p class="mt-3">
                        CISMART dapat memperbarui dokumen ini. Versi baru berlaku sejak tanggal yang tercantum di
                        bagian atas halaman ini, dan versi sebelumnya tetap tersimpan sebagai bagian dari bukti
                        persetujuan pengguna.
                    </p>
                </section>
            </div>

            <p class="mt-12 text-xs text-stone-400">
                Dokumen versi {{ $version }} ini adalah draft yang disusun untuk kebutuhan operasional CISMART dan
                belum menggantikan nasihat hukum profesional.
            </p>
        </div>
    </div>
</x-layouts.app>