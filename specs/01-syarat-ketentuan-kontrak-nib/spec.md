# Specification — plan-01 Halaman Syarat & Ketentuan, Perjanjian Penjual, dan unggah NIB

## 1. Overview

Repo ini sudah menjalankan aplikasi CISMART lengkap (katalog, checkout, QRIS, permohonan penjual, panel admin) tetapi belum punya satu pun spesifikasi; seluruh perilaku yang ada sekarang adalah **baseline tanpa spec** dan bukan milik plan mana pun. Plan ini adalah delta pertama: menyediakan halaman **Syarat & Ketentuan** yang bisa dibaca publik, halaman **Perjanjian Penjual** yang harus disetujui calon penjual sebelum mengirim permohonan, jejak persetujuan yang bisa dibuktikan, dan kewajiban mengunggah **Izin Usaha (NIB)**. Penggunanya adalah calon penjual UMKM dan admin yang meninjau permohonan. Pemicunya: permohonan penjual sekarang bisa dikirim tanpa dasar hukum apa pun yang tercatat, dan admin menilai kelayakan usaha tanpa melihat izin usahanya.

## 2. Problem Statement

Hari ini `GET /register/umkm` menerima permohonan tanpa satu pun pernyataan persetujuan; tidak ada halaman yang bisa ditunjuk sebagai syarat layanan, dan footer punya tombol "Syarat & Ketentuan" yang tidak mengarah ke mana pun. Akibatnya, ketika terjadi sengketa (barang tidak sesuai, penjual berhenti melayani, data pembeli disalahgunakan), CISMART tidak punya dokumen yang disetujui penjual maupun bukti kapan dan versi mana yang disetujui. Di sisi lain, kolom "Dokumen Pendukung (KTP/SIUP)" bersifat bebas: satu file apa saja diloloskan, sehingga admin bisa menyetujui usaha yang izin usahanya tidak pernah dilihat.

## 3. Goals

- **G-001** — Pengunjung tanpa akun dapat membaca Syarat & Ketentuan di satu URL tetap dan tahu versi serta tanggal berlakunya.
- **G-002** — Calon penjual membaca kontrak penjual utuh sebelum mencentang, dan sistem menolak permohonan yang dikirim tanpa persetujuan.
- **G-003** — Setiap persetujuan bisa dibuktikan: siapa, versi dokumen apa, kapan, dan dari alamat IP mana.
- **G-004** — Setiap permohonan yang masuk ke admin menyertakan file NIB yang bisa dibuka langsung dari panel.
- **G-005** — Perbaikan permohonan yang ditolak tidak menghapus NIB atau persetujuan yang sudah sah.

## 4. Non-Goals

- **Halaman Kebijakan Privasi, Bantuan/FAQ, dan Blog** — tombolnya ada di footer tapi tidak diminta; dibiarkan apa adanya sampai ada plan sendiri.
- **Editor teks legal di panel admin** — sengaja ditolak; teks ada di Blade (lihat §18), sehingga mengubah isi butuh deploy.
- **Tanda tangan elektronik / e-signature** — persetujuan cukup berupa checkbox + baris audit, bukan sertifikat digital.
- **Verifikasi NIB otomatis ke API OSS** — admin memeriksa manual; integrasi OSS tidak diminta dan menambah kredensial baru.
- **Riwayat persetujuan di panel admin** — plan ini menyimpan datanya, tapi belum menyediakan layar daftar/riwayatnya.
- **Notifikasi email ketika versi kontrak naik** — penjual hanya diminta menyetujui ulang saat ia membuka form permohonan lagi.
- **Persetujuan syarat untuk pembeli saat daftar** — permintaan eksplisit hanya untuk penjual.
- **Menggabungkan NIB ke dalam "Dokumen Pendukung"** — dua field tetap terpisah agar admin bisa membedakan syarat izin usaha dari lampiran tambahan.

## 5. Target Users

- **Calon penjual UMKM** — pemilik usaha di Cilacap yang mendaftar lewat `/register/umkm`; sering mengurus sendiri, memakai ponsel, dan file NIB-nya berupa PDF hasil unduhan OSS atau foto lembar cetak.
- **Admin/operator CISMART** — meninjau permohonan di `/admin`, butuh melihat NIB tanpa mengunduh dulu, dan butuh tahu permohonan itu sudah disetujui kontrak versi berapa.
- **Pembeli** — tidak punya tugas baru di plan ini; hanya mendapat kepastian bahwa penjual terikat aturan.

## 6. User Roles

| Role | Boleh apa di plan ini |
|---|---|
| Guest (belum login) | Membaca `/syarat-ketentuan` dan `/perjanjian-penjual`; mengisi form permohonan penjual termasuk mencentang persetujuan dan mengunggah NIB. |
| Buyer (sudah login) | Sama seperti guest, tetapi form permohonan mengunci data akun. |
| Seller (permohonan disetujui) | Tidak punya alur baru; hanya bisa melihat NIB yang ia unggah di halaman status. |
| Admin | Melihat dan membuka NIB setiap permohonan di panel; tidak mengubah isi dokumen legal. |

## 7. User Stories

- **US-001** — Sebagai calon penjual, saya ingin membaca Perjanjian Penjual selengkapnya sebelum mencentang, supaya saya tahu kewajiban apa yang saya ambil.
- **US-002** — Sebagai calon penjual, saya ingin diberi tahu dengan jelas bahwa saya belum menyetujui kontrak, supaya saya tidak bingung kenapa permohonan tidak terkirim.
- **US-003** — Sebagai calon penjual, saya ingin mengunggah NIB dalam bentuk PDF atau foto, supaya saya tidak perlu memindai ulang dokumen.
- **US-004** — Sebagai calon penjual yang permohonannya ditolak, saya ingin memperbaiki data tanpa mengunggah ulang NIB dan tanpa mencentang ulang kontrak yang sama, supaya pengajuan ulang cepat.
- **US-005** — Sebagai admin, saya ingin membuka NIB setiap permohonan dari panel, supaya saya bisa menolak usaha yang izinnya tidak sesuai sebelum memberi role penjual.
- **US-006** — Sebagai admin, saya ingin tahu permohonan ini disetujui pada versi kontrak berapa, supaya saya bisa menjelaskan dasar persetujuannya saat ada sengketa.

## 8. Functional Requirements

### Halaman legal

- **FR-001** — `GET /syarat-ketentuan` (nama route `legal.terms`) mengembalikan 200 untuk tamu maupun user login, memakai layout `components.layouts.app` (header + footer), dengan judul dokumen "Syarat & Ketentuan".
- **FR-002** — Halaman S&K menampilkan versi dan tanggal berlaku yang dibaca dari `config('legal.terms.version')` dan `config('legal.terms.effective_at')`, mis. "Versi 1.0 · berlaku 1 Desember 2026"; tidak ada teks versi yang ditulis ganda di dalam Blade.
- **FR-003** — Isi S&K dibagi menjadi sekurangnya delapan pasal bernomor (definisi, akun, kewajiban penjual, kewajiban pembeli, transaksi & pembayaran, barang yang dilarang, sanksi & penutupan toko, hukum yang berlaku), setiap pasal punya `id` anchor stabil, dan halaman menyediakan daftar isi yang menautkan ke anchor tersebut sehingga `/syarat-ketentuan#kewajiban-penjual` menggulir ke pasal itu.
- **FR-004** — `GET /perjanjian-penjual` (nama route `legal.seller-agreement`) mengembalikan 200 untuk tamu, memuat Perjanjian Penjual lengkap dengan versi dan tanggal berlaku dari `config('legal.seller_agreement.*')`, mencantumkan para pihak (CISMART dan penjual), kewajiban penjual, ketentuan pencairan/komisi, larangan, sanksi, dan penyelesaian sengketa.
- **FR-005** — Kedua halaman saling menautkan: S&K menautkan ke Perjanjian Penjual, Perjanjian Penjual menautkan kembali ke S&K.
- **FR-006** — Tautan "Syarat & Ketentuan" di footer menjadi anchor ke `route('legal.terms')` (hari ini berupa `<button>` tanpa aksi), dan tetap benar di setiap halaman yang merender footer.
- **FR-007** — Kedua halaman me-render tanpa query database dan tanpa data user; membukanya tidak butuh login.

### Persetujuan kontrak penjual

- **FR-008** — Form permohonan penjual (`App\Livewire\Pages\RegisterUmkmPage`) punya properti publik `agreement_accepted` (boolean, default `false`) dengan komponen checkbox.
- **FR-009** — Persetujuan wajib **hanya bila** user belum punya baris `agreement_acceptances` untuk `document_type = 'seller_agreement'` dan versi yang aktif di config. Bila sudah ada, checkbox diganti keterangan "Kamu sudah menyetujui versi {versi} pada {tanggal}" dan validasi tidak mewajibkannya.
- **FR-010** — Submit dengan `agreement_accepted = false` (padahal wajib) gagal validasi pada field `agreement_accepted` dengan pesan "Kamu harus menyetujui Perjanjian Penjual versi {versi} sebelum mengirim permohonan.", dan tidak ada baris `seller_applications` maupun `agreement_acceptances` yang dibuat/diubah.
- **FR-011** — Label checkbox memuat nama dokumen dan versinya serta tautan ke `route('legal.seller-agreement')` yang dibuka di tab baru (`target="_blank" rel="noopener noreferrer"`), sehingga isi kontrak bisa dibaca tanpa kehilangan isian form.
- **FR-012** — Ketika submit lolos validasi, baris `agreement_acceptances` ditulis **dalam transaksi yang sama** dengan pembuatan/pembaruan permohonan, berisi `user_id`, `document_type = 'seller_agreement'`, `version` aktif, `ip_address`, `user_agent`, dan `accepted_at`; penulisan bersifat idempoten (`firstOrCreate` pada `user_id + document_type + version`), jadi submit ulang tidak membuat baris kedua.
- **FR-013** — Versi baru di config menghasilkan baris persetujuan baru untuk user yang sama; baris lama tidak diubah dan tidak dihapus.

### Unggah NIB

- **FR-014** — Form permohonan punya field unggah satu file `nib_document` (bukan array), berlabel "Izin Usaha (NIB)", dengan aturan `required` bila permohonan belum punya NIB tersimpan, `file`, `mimes:pdf,jpg,jpeg,png`, `max:5120` (5 MB). Pesan galat berbahasa Indonesia untuk tipe file salah dan ukuran lebih.
- **FR-015** — File NIB disimpan di disk `public` pada folder `seller-documents/nib`, dan path relatifnya disimpan di kolom baru `seller_applications.nib_document_path`.
- **FR-016** — Submit ulang tanpa memilih file baru mempertahankan path lama, tidak menghapus file lama, dan tidak menggagalkan validasi; bila file baru dipilih, path diganti dan file lama dihapus setelah transaksi sukses.
- **FR-017** — Halaman status permohonan (kondisi `pending` dan `approved` di view yang sama) menampilkan baris "Izin Usaha (NIB)" dengan tautan ke file, dan menampilkan "Belum diunggah" bila path kosong (hanya mungkin untuk data lama).
- **FR-018** — Resource Filament permohonan penjual (`SellerApplicationResource`) menampilkan NIB yang bisa dibuka admin, di samping "Dokumen Pendukung" yang sudah ada; pada halaman view field bersifat read-only dan tidak ikut ter-dehydrate.
- **FR-019** — Submit dengan NIB bertipe selain PDF/JPG/PNG, atau lebih dari 5 MB, gagal validasi dan tidak membuat/mengubah permohonan.

### Skema dan konfigurasi

- **FR-020** — Satu migration baru menambah kolom `nib_document_path` (string, nullable) pada `seller_applications` dan membuat tabel `agreement_acceptances`; `down()` menghapus keduanya dengan bersih.
- **FR-021** — Baris `seller_applications` yang sudah ada tetap valid tanpa backfill: `nib_document_path` bernilai `null` dan user tersebut diminta mengunggah NIB saat mengajukan ulang.
- **FR-022** — File `config/legal.php` baru memuat `terms` dan `seller_agreement`, masing-masing dengan `version` (string) dan `effective_at` (tanggal), dan menjadi satu-satunya sumber versi yang dipakai halaman legal, validasi, dan baris persetujuan.

## 9. Business Logic

- **BL-001** — Versi dokumen adalah teks di config (`legal.terms.version`, `legal.seller_agreement.version`). Menaikkan versi adalah tindakan sadar yang membuat penjual diminta menyetujui ulang pada submit berikutnya; plan ini tidak menyediakan mekanisme paksa bagi penjual yang sudah aktif.
- **BL-002** — Satu user menyimpan paling banyak satu baris persetujuan per (`document_type`, `version`); ini ditegakkan unique index, bukan hanya oleh kode.
- **BL-003** — Persetujuan dicatat untuk user yang benar-benar mengirim permohonan. Untuk tamu, user dibuat lebih dulu di dalam alur submit yang sudah ada, lalu persetujuan dicatat atas user itu; akun yang dibuat tapi submit gagal tidak boleh meninggalkan baris persetujuan.
- **BL-004** — NIB wajib bila `nib_document_path` masih kosong. Setelah ada, field boleh kosong pada submit berikutnya karena file lama dipakai. Aturan ini menggantikan anggapan "dokumen pendukung mewakili izin usaha": keduanya sekarang syarat terpisah.
- **BL-005** — Menolak permohonan (`status = rejected`) tidak menghapus NIB maupun baris persetujuan; penolakan hanya membuka form untuk diajukan ulang.
- **BL-006** — Menyetujui permohonan tetap menempuh jalur lama (`SellerApplication::provisionShop()`, role `seller` + toko otomatis). Plan ini tidak mengubah alur persetujuan; ia hanya menambah informasi yang tersedia bagi admin saat memutuskan.
- **BL-007** — Data legal dan persetujuan bersifat bukti: baris `agreement_acceptances` tidak di-update setelah dibuat.

## 10. User Flow

**A. Membaca Syarat & Ketentuan**

1. Pengunjung menekan "Syarat & Ketentuan" di footer (atau membuka URL langsung).
2. Sistem merender halaman S&K dengan badge versi + tanggal berlaku dan daftar isi delapan pasal.
3. Pengunjung menggulir atau menekan satu pasal di daftar isi → halaman menggulir ke anchor pasal itu.
4. Dari salah satu pasal ia bisa menuju Perjanjian Penjual.

**B. Calon penjual mengirim permohonan**

1. Calon penjual membuka `/register/umkm`. Bila tamu, ia melihat bagian akun; bila login, bagian akun disembunyikan.
2. Ia mengisi data akun dan data usaha, lalu bagian "Izin Usaha (NIB)".
3. Di bawah field NIB ia melihat checkbox "Saya telah membaca dan menyetujui Perjanjian Penjual versi 1.0" dengan tautan yang membuka kontrak di tab baru.
4. Ia menekan "Kirim Permohonan".
   - **Cabang galat (kontrak)** — checkbox tidak dicentang: pesan di bawah checkbox, tidak ada data yang tersimpan, isian lain tetap utuh.
   - **Cabang galat (NIB)** — file belum dipilih, bertipe lain, atau > 5 MB: pesan pada field NIB, tidak ada data yang tersimpan.
   - **Cabang sukses** — permohonan dibuat (`pending`), baris persetujuan tercatat, notifikasi sukses muncul, dan form berganti menjadi kartu status "Permohonan sedang ditinjau" yang menampilkan NIB yang baru diunggah.
5. **Pengajuan ulang setelah ditolak** — form terbuka lagi dengan data lama. NIB dan persetujuan versi yang sama tidak diminta ulang; mengirim ulang memperbarui baris permohonan yang sama.

**C. Admin meninjau**

1. Admin membuka `/admin/seller-applications` dan memilih satu permohonan.
2. Ia membaca data usaha, membuka NIB di tab baru, lalu memutuskan setuju/tolak.
3. Persetujuan berjalan seperti sebelumnya: status `approved` → role `seller` dan toko aktif.

## 11. Technical Requirements

- Stack tetap: Laravel 12 / PHP 8.4, Livewire 3 (`WithFileUploads` sudah dipakai halaman ini), Filament 4, Tailwind v4, Pest. Tidak ada dependency composer/npm baru.
- Halaman legal adalah Blade murni (tanpa komponen Livewire) agar bisa di-cache dan tidak menambah round-trip; route didaftarkan di `routes/web.php` dengan nama `legal.*`.
- Migration baru bertimestamp lebih baru dari `2025_09_25_124541`; migration lama tidak diedit.
- **Batas unggah PHP harus dinaikkan**: image saat ini tidak mengatur `upload_max_filesize`/`post_max_size` (bawaan 2 MB / 8 MB), sehingga file 5 MB akan ditolak PHP sebelum validasi Laravel jalan. Tambahkan konfigurasi di `docker/php/Dockerfile` (`upload_max_filesize=10M`, `post_max_size=12M`) dan catat di `docs/DEPLOYMENT_GUIDE.md` bahwa nilai ini bisa ditimpa lewat env/ini di server.
- Validasi Laravel (`max:5120`) adalah gerbang utama; batas PHP hanya mencegah file terpotong diam-diam.
- Test baru ada di `laravel_app/tests/Feature` dengan `Storage::fake('public')`; tidak ada test yang menyentuh disk nyata.
- Kinerja: halaman legal ≤ 1 query (nol), dan submit permohonan tidak menambah query lebih dari tiga (cek persetujuan, insert persetujuan, insert/update permohonan).

## 12. Security Requirements

- **SR-001** — NIB dan KTP bukan data publik untuk pengunjung anonim, tapi juga bukan rahasia tingkat tinggi: NIB terdaftar di OSS. Plan ini tetap memakai disk `public` seperti dokumen yang sudah ada, dengan nama file acak hasil `store()` (tidak bisa ditebak dari nama usaha), dan tautannya hanya dirender di halaman status milik user sendiri serta panel admin. Risiko ini dicatat di §17.
- **SR-002** — Halaman status hanya bisa dibuka pemilik permohonan (middleware/threshold yang sudah ada); user lain tidak boleh melihat NIB orang lain. Test harus membuktikannya.
- **SR-003** — Field `nib_document_path` dan `agreement_acceptances.*` tidak boleh bisa diisi dari input pengguna di luar alur: path selalu dari hasil `store()`, `user_id` selalu dari user yang terautentikasi, `accepted_at` selalu `now()`, `ip_address`/`user_agent` dari request saat itu.
- **SR-004** — Baris `agreement_acceptances` bersifat append-only: tidak ada endpoint, tidak ada `update()`, dan model tidak menyediakan mass-assignment untuk mengubah `version`/`accepted_at`.
- **SR-005** — Validasi tipe file memakai `mimes:` + pemeriksaan MIME nyata Laravel, bukan hanya ekstensi; file disimpan di luar `public/build` dan tidak pernah dieksekusi sebagai PHP (folder unggahan tidak punya handler eksekusi).
- **SR-006** — Rate limit submit yang sudah ada (`5` per `600` detik per user/IP) tetap berlaku dan berlaku juga untuk percobaan yang gagal validasi NIB.
- **SR-007** — Tautan dokumen di halaman legal dan kontrak tidak memuat `target="_blank"` tanpa `rel="noopener noreferrer"`.

## 13. Scalability Considerations

- Beban baru bersifat tulis jarang: satu baris `agreement_acceptances` per user per versi, dan satu file NIB per permohonan. Unique index `(user_id, document_type, version)` menjaga volume tetap.
- Bottleneck yang realistis adalah ukuran disk, bukan CPU: 5 MB × jumlah permohonan. Bila permohonan melewati ±50/hari, pindahkan folder `seller-documents` ke object storage — tidak perlu di plan ini.
- Halaman legal murni Blade dan identik untuk semua pengunjung, jadi aman di-cache HTTP penuh; tidak ada data per-user yang perlu diinvalidasi.
- Tabel `agreement_acceptances` tumbuh terbatas (satu baris per user per versi) sehingga tidak butuh partisi atau arsip dalam horizon beberapa tahun.

## 14. Data Requirements

**Tabel baru `agreement_acceptances`**

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK `users` | `cascadeOnDelete` |
| `document_type` | string(50) | nilai plan ini: `seller_agreement` |
| `version` | string(20) | dari config saat persetujuan dicatat |
| `ip_address` | string(45) | mendukung IPv6 |
| `user_agent` | text, nullable | |
| `accepted_at` | timestamp | waktu server |

- Unique index `(user_id, document_type, version)`; index tambahan `(document_type, version)` untuk laporan per versi.
- Tanpa `created_at`/`updated_at`: baris tidak pernah diperbarui.
- Retensi: disimpan selama akun ada; saat akun dihapus, barisnya ikut terhapus karena bukan kewajiban penyimpanan jangka panjang di plan ini.

**Kolom baru `seller_applications.nib_document_path`**

- `string`, nullable, path relatif di disk `public` (mis. `seller-documents/nib/abc123.pdf`).
- Data lama: `null` — tidak ada backfill, dan user lama diminta mengunggah saat mengajukan ulang.

**Model**

- Model baru `AgreementAcceptance` (`user_id`, `document_type`, `version`, `ip_address`, `user_agent`, `accepted_at` di `$fillable`; `accepted_at` cast `datetime`; helper statis `record(User $user, string $type, string $version, Request $request)` yang idempoten).
- `User::agreementAcceptances()` (`hasMany`) dan `User::hasAcceptedDocument(string $type, string $version): bool`.
- `SellerApplication` menambah `nib_document_path` ke `$fillable` dan helper `hasNib(): bool`.

## 15. Integration Requirements

None. Plan ini tidak menambah integrasi eksternal: NIB diperiksa manual oleh admin dan tidak ada panggilan ke OSS, payment gateway, atau service ML. Kontrak ML yang ada (`App\Services\MLService`) tidak tersentuh.

## 16. Constraints

- Berjalan di image Docker yang ada; perubahan batas unggah PHP harus dilakukan di `docker/php/Dockerfile` dan tetap bisa ditimpa di server produksi.
- Semua teks pengguna berbahasa Indonesia, nada sama dengan halaman lain (kalimat lengkap, tanpa emoji).
- Migration tidak boleh memutus data atau test yang sudah ada; suite Pest harus tetap hijau tanpa perubahan pada test lama.
- Isi legal adalah **draft v1.0 yang ditulis developer, bukan hasil tinjauan ahli hukum**; teks harus diberi catatan bahwa ia belum menggantikan nasihat hukum.
- Halaman legal tidak boleh bergantung pada panel admin, queue, atau service ML agar tetap bisa dibuka saat komponen lain mati.
- Disk `public` memerlukan symlink `storage:link` yang sudah menjadi bagian dari setup Docker; deployment baru harus memastikan symlink itu ada.

## 17. Open Questions

| # | Pertanyaan | Default bila tidak dijawab | Menghambat slice? |
|---|---|---|---|
| Q-001 | Perlukah Kebijakan Privasi masuk plan ini karena footer sudah menampilkannya? | Tidak — dibiarkan tombol mati, dibuat di plan terpisah. | Tidak |
| Q-002 | Perlukah dokumen penjual pindah dari disk `public` ke disk privat + signed route? | Tidak untuk plan ini; dicatat sebagai risiko di §12/§13. | Tidak |
| Q-003 | Apakah penjual yang sudah `approved` sebelum plan ini wajib menyetujui kontrak (backfill consent)? | Tidak — hanya saat pengajuan berikutnya; tidak ada UI untuk memaksa. | Tidak |
| Q-004 | Apakah teks legal perlu ditinjau ahli hukum sebelum rilis? | Ya, tapi tidak menahan implementasi; versi 1.0 dirilis sebagai draft. | Tidak |
| Q-005 | Apakah admin perlu melihat riwayat persetujuan per versi di panel? | Tidak di plan ini; datanya sudah tersimpan untuk plan berikutnya. | Tidak |

## 18. Decisions

| # | Tanggal | Keputusan | Alternatif yang dikorbankan |
|---|---|---|---|
| D-001 | 2026-11-16 | Teks S&K dan Perjanjian Penjual ditulis di Blade statis, versi + tanggal berlaku di `config/legal.php`. | Tabel `legal_documents` + Filament (admin bisa mengubah tanpa deploy) — dikorbankan karena menambah migrasi, resource, dan alur publikasi versi yang belum dibutuhkan; file Markdown di `resources/legal` — dikorbankan karena tetap butuh deploy tapi tanpa pratinjau yang jelas. |
| D-002 | 2026-11-16 | Perjanjian Penjual adalah halaman sendiri (`/perjanjian-penjual`); form hanya menampilkan ringkasan + tautan. | Satu halaman S&K dengan anchor `#penjual` — dikorbankan karena dokumen penjual dan dokumen umum punya audiens dan versi berbeda; modal scroll di form — dikorbankan karena tidak memberi URL yang bisa dirujuk saat sengketa. |
| D-003 | 2026-11-16 | Persetujuan disimpan di tabel `agreement_acceptances` (per user, per jenis, per versi) dengan IP dan user-agent. | Kolom `agreement_version` + `agreement_accepted_at` di `seller_applications` — dikorbankan karena tertimpa saat pengajuan ulang dan hanya menampung satu kontrak; validasi checkbox tanpa jejak — dikorbankan karena tidak bisa dibuktikan. |
| D-004 | 2026-11-16 | NIB wajib, satu file, PDF/JPG/PNG, maks 5 MB, disimpan di kolom sendiri. | Wajib PDF saja maks 2 MB — dikorbankan karena banyak pendaftar hanya memotret lembar cetak; opsional — dikorbankan karena admin kembali menilai tanpa izin usaha. |
| D-005 | 2026-11-16 | "Dokumen Pendukung (KTP/SIUP)" dibiarkan apa adanya; NIB adalah syarat tambahan, bukan pengganti. | Menghapus field lama dan meminta NIB + KTP secara eksplisit — dikorbankan karena mengubah alur yang sudah berjalan dan menghapus data lama tanpa manfaat. |
| D-006 | 2026-11-16 | Persetujuan wajib hanya bila versi aktif belum pernah disetujui user tersebut. | Mewajibkan centang setiap submit — dikorbankan karena menghukum pengajuan ulang yang tidak ada hubungannya dengan kontrak. |
| D-007 | 2026-11-16 | Batas unggah PHP dinaikkan di `docker/php/Dockerfile` (10M/12M). | Menurunkan batas file NIB ke 2 MB — dikorbankan karena hasil pindai PDF NIB lazim 2–4 MB. |

## 19. Acceptance Criteria

- **AC-001** (G-001, FR-001, FR-002) — `GET /syarat-ketentuan` mengembalikan 200 untuk tamu, memuat teks versi dari `config('legal.terms.version')` dan tanggal berlaku, dan memakai header + footer aplikasi.
- **AC-002** (FR-003) — Halaman S&K memuat ≥ 8 elemen dengan `id` anchor pasal, dan setiap tautan daftar isi menunjuk ke salah satu `id` tersebut.
- **AC-003** (G-002, FR-004, FR-005) — `GET /perjanjian-penjual` mengembalikan 200 untuk tamu, memuat versi kontrak dan tautan balik ke S&K.
- **AC-004** (FR-006) — Halaman beranda merender tautan footer `Syarat & Ketentuan` dengan `href` yang sama dengan `route('legal.terms')`.
- **AC-005** (G-002, FR-010) — Mengirim form permohonan tanpa `agreement_accepted` menghasilkan galat validasi pada field `agreement_accepted`, `seller_applications` dan `agreement_acceptances` tetap kosong, dan isian usaha tidak hilang.
- **AC-006** (G-003, FR-012) — Submit yang lolos membuat tepat satu baris `agreement_acceptances` dengan `version` = versi config, `accepted_at` terisi, dan `ip_address` sama dengan IP request test.
- **AC-007** (FR-012, FR-013) — Submit ulang pada versi yang sama tidak menambah baris persetujuan kedua (tetap satu), dan menaikkan versi di config lalu submit lagi menghasilkan baris kedua tanpa mengubah baris pertama.
- **AC-008** (G-004, FR-014, FR-019) — Submit dengan NIB ber-ekstensi `.exe` atau berukuran 6 MB gagal validasi pada field `nib_document` dan tidak menyimpan file; submit dengan PDF 1 MB berhasil dan menyimpan file di `seller-documents/nib` pada disk `public`.
- **AC-009** (G-005, FR-016) — Permohonan yang sudah punya NIB bisa dikirim ulang tanpa memilih file, dan `nib_document_path` tetap bernilai path lama.
- **AC-010** (FR-017) — Halaman status permohonan pemilik menampilkan tautan NIB; halaman yang sama untuk user lain tidak menampilkan NIB milik user pertama.
- **AC-011** (FR-018) — Halaman view `SellerApplicationResource` merender NIB yang tersimpan sehingga admin bisa membukanya; tanpa path, panel menampilkan keadaan kosong tanpa galat.
- **AC-012** (FR-020, FR-021) — `php artisan migrate` pada database berisi permohonan lama berhasil tanpa backfill, dan `migrate:rollback` satu langkah mengembalikan skema ke keadaan sebelumnya.
- **AC-013** — Suite Pest yang sudah ada tetap lulus tanpa perubahan, dan test baru untuk plan ini lulus di `php artisan test`.