@php
    $version = config('legal.seller_agreement.version');
    $effectiveAt = \Illuminate\Support\Carbon::parse(config('legal.seller_agreement.effective_at'));
@endphp

<x-layouts.app>
    <div class="bg-stone-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 py-14 sm:py-20">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Dokumen Legal</p>
            <h1 class="mt-2 text-3xl sm:text-4xl font-bold tracking-tight text-stone-900">Perjanjian Penjual</h1>
            <p class="mt-3 text-sm text-stone-500">
                Versi {{ $version }} &middot; berlaku {{ $effectiveAt->translatedFormat('j F Y') }}
            </p>

            <div class="mt-6 rounded-2xl border border-green-200 bg-green-50/60 p-5 text-sm text-stone-700 leading-relaxed">
                <p>
                    Perjanjian ini dibuat antara <strong>Pengelola CISMART</strong> ("CISMART") dan
                    <strong>penjual</strong> yang mendaftar melalui halaman permohonan UMKM ("Penjual").
                    Dengan mencentang persetujuan pada form pendaftaran, Penjual menyatakan telah membaca,
                    memahami, dan menyetujui seluruh isi perjanjian ini.
                </p>
                <p class="mt-3">
                    Versi yang disetujui dicatat bersama waktu dan alamat IP saat pendaftaran, dan menjadi dasar
                    hubungan hukum antara CISMART dan Penjual.
                </p>
            </div>

            <nav aria-label="Daftar isi" class="mt-8 rounded-2xl border border-stone-200 bg-white p-5">
                <p class="text-sm font-semibold text-stone-900 mb-3">Daftar isi</p>
                <ol class="grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm text-green-700">
                    <li><a href="#ruang-lingkup" class="hover:text-green-800 hover:underline">1. Ruang lingkup</a></li>
                    <li><a href="#izin-usaha" class="hover:text-green-800 hover:underline">2. Izin usaha &amp; dokumen</a></li>
                    <li><a href="#kewajiban" class="hover:text-green-800 hover:underline">3. Kewajiban Penjual</a></li>
                    <li><a href="#produk" class="hover:text-green-800 hover:underline">4. Produk &amp; harga</a></li>
                    <li><a href="#pesanan" class="hover:text-green-800 hover:underline">5. Pesanan &amp; pengiriman</a></li>
                    <li><a href="#pembayaran" class="hover:text-green-800 hover:underline">6. Pembayaran &amp; pencairan</a></li>
                    <li><a href="#larangan" class="hover:text-green-800 hover:underline">7. Larangan</a></li>
                    <li><a href="#sanksi" class="hover:text-green-800 hover:underline">8. Sanksi &amp; pengakhiran</a></li>
                    <li><a href="#data" class="hover:text-green-800 hover:underline">9. Data &amp; kerahasiaan</a></li>
                    <li><a href="#sengketa" class="hover:text-green-800 hover:underline">10. Penyelesaian sengketa</a></li>
                    <li><a href="#perubahan" class="hover:text-green-800 hover:underline">11. Perubahan perjanjian</a></li>
                    <li><a href="#hukum" class="hover:text-green-800 hover:underline">12. Hukum yang berlaku</a></li>
                </ol>
            </nav>

            <div class="mt-10 space-y-10 text-stone-700 leading-relaxed">
                <section id="ruang-lingkup" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">1. Ruang lingkup</h2>
                    <p class="mt-3">
                        CISMART menyediakan tempat berjualan daring: halaman toko, katalog produk, keranjang,
                        pemrosesan pesanan, dan alat komunikasi dengan pembeli. CISMART bukan pihak dalam perjanjian
                        jual beli antara Penjual dan pembeli, dan tidak menjadi penjamin mutu produk yang dijual.
                    </p>
                    <p class="mt-3">
                        Perjanjian ini berlaku sejak permohonan Penjual disetujui dan tetap berlaku selama toko
                        beroperasi di CISMART.
                    </p>
                </section>

                <section id="izin-usaha" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">2. Izin usaha &amp; dokumen</h2>
                    <ul class="mt-3 space-y-2 list-disc pl-5">
                        <li>Penjual wajib mengunggah dokumen <strong>Izin Usaha (NIB)</strong> dalam format PDF atau gambar, dan dokumen pendukung lain yang diminta.</li>
                        <li>Dokumen yang diunggah harus milik usaha yang didaftarkan, masih berlaku, dan terbaca jelas.</li>
                        <li>CISMART memeriksa dokumen secara manual dan dapat menolak atau meminta perbaikan bila dokumen tidak sesuai.</li>
                        <li>Perubahan nomor izin, alamat usaha, atau badan usaha wajib diperbarui oleh Penjual.</li>
                        <li>Dokumen yang diunggah disimpan sebagai bagian dari berkas permohonan dan tidak dihapus oleh penolakan permohonan.</li>
                    </ul>
                </section>

                <section id="kewajiban" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">3. Kewajiban Penjual</h2>
                    <ul class="mt-3 space-y-2 list-disc pl-5">
                        <li>Menjaga kebenaran data usaha, kontak, dan rekening penerimaan dana.</li>
                        <li>Menampilkan produk secara jujur: nama, deskripsi, komposisi, ukuran, masa simpan, dan foto yang mewakili barang sebenarnya.</li>
                        <li>Menjaga ketersediaan stok dan memperbaruinya ketika habis.</li>
                        <li>Memproses pesanan yang masuk dalam batas waktu yang ditampilkan di dashboard penjual.</li>
                        <li>Mengemas produk dengan aman, terutama produk makanan.</li>
                        <li>Menangani keluhan pembeli dengan itikad baik sebelum melibatkan CISMART.</li>
                    </ul>
                </section>

                <section id="produk" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">4. Produk &amp; harga</h2>
                    <p class="mt-3">
                        Penjual menetapkan harganya sendiri. Harga yang ditampilkan harus sudah mencakup pajak yang
                        berlaku atas produk tersebut; ongkos kirim dihitung terpisah saat checkout. Penjual dilarang
                        menaikkan harga secara sepihak setelah pesanan dibayar.
                    </p>
                </section>

                <section id="pesanan" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">5. Pesanan &amp; pengiriman</h2>
                    <p class="mt-3">
                        Pesanan yang tidak dikonfirmasi Penjual dalam batas waktu yang berlaku akan dibatalkan
                        otomatis oleh sistem. Pesanan yang dibatalkan karena kelalaian Penjual dapat memengaruhi
                        penilaian toko.
                    </p>
                    <p class="mt-3">
                        Pengiriman mengikuti metode yang dipilih pembeli saat checkout, termasuk pengambilan langsung
                        di toko bila tersedia.
                    </p>
                </section>

                <section id="pembayaran" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">6. Pembayaran &amp; pencairan</h2>
                    <p class="mt-3">
                        Pembayaran pembeli diproses melalui penyedia pembayaran QRIS. Dana diteruskan kepada Penjual
                        setelah pesanan selesai dan tidak berada dalam sengketa. CISMART tidak menyimpan dana Penjual
                        untuk tujuan lain.
                    </p>
                    <p class="mt-3">
                        Biaya layanan, bila diberlakukan, akan diberitahukan sebelum diberlakukan dan menjadi bagian
                        dari versi perjanjian ini pada saat itu.
                    </p>
                </section>

                <section id="larangan" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">7. Larangan</h2>
                    <ul class="mt-3 space-y-2 list-disc pl-5">
                        <li>Menjual barang yang dilarang pada Syarat &amp; Ketentuan bagian 6.</li>
                        <li>Menggunakan foto atau merek milik pihak lain tanpa izin.</li>
                        <li>Membuat ulasan atau pesanan palsu untuk menaikkan penilaian toko.</li>
                        <li>Mengarahkan pembeli bertransaksi di luar CISMART untuk menghindari pencatatan pesanan.</li>
                        <li>Mengakses data pembeli di luar keperluan memproses pesanan.</li>
                    </ul>
                </section>

                <section id="sanksi" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">8. Sanksi &amp; pengakhiran</h2>
                    <p class="mt-3">
                        CISMART dapat menyembunyikan produk, menangguhkan toko, atau mengakhiri perjanjian ini bila
                        Penjual melanggar ketentuan di atas. Untuk pelanggaran ringan, CISMART memberi kesempatan
                        perbaikan lebih dahulu.
                    </p>
                    <p class="mt-3">
                        Penjual dapat berhenti berjualan kapan saja dengan menyelesaikan pesanan yang masih berjalan.
                        Pengakhiran tidak menghapus kewajiban yang sudah timbul sebelumnya.
                    </p>
                </section>

                <section id="data" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">9. Data &amp; kerahasiaan</h2>
                    <p class="mt-3">
                        CISMART memproses data Penjual dan data pembeli untuk menjalankan layanan ini. Penjual hanya
                        boleh menggunakan data pembeli untuk memproses pesanannya, dan dilarang menyebarkannya.
                        Dokumen izin usaha dapat diperiksa oleh petugas CISMART yang berwenang.
                    </p>
                </section>

                <section id="sengketa" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">10. Penyelesaian sengketa</h2>
                    <p class="mt-3">
                        Sengketa antara Penjual dan pembeli diselesaikan terlebih dahulu secara musyawarah, dengan
                        CISMART sebagai penengah bila diperlukan. Bila tidak tercapai kesepakatan, para pihak dapat
                        menempuh jalur hukum sesuai bagian 12.
                    </p>
                </section>

                <section id="perubahan" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">11. Perubahan perjanjian</h2>
                    <p class="mt-3">
                        CISMART dapat memperbarui perjanjian ini. Versi baru memiliki nomor versi dan tanggal berlaku
                        sendiri; Penjual akan diminta menyetujui versi terbaru ketika memperbarui data permohonannya.
                        Persetujuan atas versi sebelumnya tetap tersimpan sebagai bukti.
                    </p>
                </section>

                <section id="hukum" class="scroll-mt-24">
                    <h2 class="text-xl font-bold text-stone-900">12. Hukum yang berlaku</h2>
                    <p class="mt-3">
                        Perjanjian ini diatur oleh hukum Republik Indonesia. Sengketa yang tidak dapat diselesaikan
                        secara musyawarah diselesaikan melalui pengadilan yang berwenang di wilayah Cilacap,
                        Jawa Tengah.
                    </p>
                </section>
            </div>

            <div class="mt-12 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                <a href="{{ route('legal.terms') }}" class="text-green-700 font-medium hover:text-green-800 hover:underline">
                    Baca juga Syarat &amp; Ketentuan
                </a>
                <span class="text-stone-300">|</span>
                <a href="{{ route('register.umkm') }}" class="text-stone-500 hover:text-green-700">Kembali ke form pendaftaran UMKM</a>
            </div>

            <p class="mt-8 text-xs text-stone-400">
                Dokumen versi {{ $version }} ini adalah draft yang disusun untuk kebutuhan operasional CISMART dan
                belum menggantikan nasihat hukum profesional.
            </p>
        </div>
    </div>
</x-layouts.app>