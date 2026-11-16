# Audit Alur & Logika — CISMART-2

Tanggal: 2026-11-10
Metode: penelusuran kode (Laravel 12 + Livewire/Volt + Filament 4 + Flask ML), verifikasi empiris pada container yang berjalan (`/products`, skema DB, `php -r`, probe test Pest sementara).
Catatan: probe test sudah dihapus. DB dev sempat ter-`migrate:fresh` oleh test run (lihat F-20) dan sudah di-seed ulang (`admin@cismart.test`).

Legenda severity: **K** = kritis (uang/data hilang), **T** = tinggi, **S** = sedang, **R** = rendah.

---

## Ringkasan

| ID | Severity | Temuan | Alur |
|-----|-----|--------|------|
| F-01 | K | Stok tidak pernah berkurang / tidak divalidasi saat checkout | Beli → pesan |
| F-02 | K | Sort "Rating Tertinggi"/"Terpopuler" & filter rating menembak kolom `products.rating`/`sold` yang tidak ada → HTTP 500 | Katalog |
| F-03 | K | Harga & subtotal pesanan diambil dari snapshot session keranjang, bukan dari DB saat checkout; produk tidak divalidasi ulang | Checkout |
| F-04 | K | Quantity keranjang tanpa batas stok (addToCart menumpuk, updateQuantity bebas) | Keranjang |
| F-05 | K | Order lintas toko diperbolehkan tetapi toko = null → ongkir 0, QRIS mati, satu status untuk banyak penjual | Checkout |
| F-06 | K | Pesanan QRIS bisa `confirmed` → `delivered` tanpa pembayaran terverifikasi, dan dihitung sebagai pendapatan | Pesanan/uang |
| F-07 | T | Ulasan toko hanya syarat "punya order apa pun" (pending/unpaid/cancelled ikut) | Ulasan |
| F-08 | T | `products.rating` tidak ada & review hanya ke toko → rating produk selalu 0, tidak bisa dinilai per produk | Ulasan/katalog |
| F-09 | T | Tidak ada pembatalan pembeli, tidak ada expiry order unpaid → order menggantung, stok tidak kembali | Pesanan |
| F-10 | T | Tidak ada notifikasi (WA/email/DB): `shop_whatsapp` & `whatsapp_sent_at` tak pernah dipakai | Pesanan |
| F-11 | T | Tidak ada rate limit pada login/register (brute force password, spam akun) | Auth |
| F-12 | T | Email tidak diverifikasi, tidak ada reset password, kolom `two_factor_*` mati, remember-me dipaksa `true` | Auth |
| F-13 | T | Role `seller` diberikan saat submit permohonan (sebelum approve) → pemohon pending bisa masuk `/admin` | Penjual |
| F-14 | T | Tak ada log riwayat status order; `confirmed_at`/`cancelled_at` diisi manual; admin bisa hapus order permanen | Pesanan |
| F-15 | T | Route debug `/test-signed` & `/make-test-url` ikut ter-deploy | Ops |
| F-16 | S | ML service tanpa auth, CORS `*`, port 5000 dipublish; `/api/train` & `/api/download` terbuka | ML |
| F-17 | S | Training sinkron di request (timeout 300 s, 2 worker tanpa lock) → model ditimpa saat dipakai | ML |
| F-18 | S | File training menumpuk tanpa retensi di volume `ml_data` | ML |
| F-19 | S | Image app: gd tanpa `libjpeg-dev` → `imagejpeg`/`imagecreatefromjpeg` tidak ada: 2 test merah + decode QRIS JPEG gagal | Ops |
| F-20 | S | `phpunit.xml` `<env>` tanpa `force` + docker env `DB_CONNECTION=pgsql` → suite jalan di DB dev dan `migrate:fresh` menghapus data dev | Ops/test |
| F-21 | S | Tidak ada CI; semua temuan di atas hanya tertangkap manual | Ops |
| F-22 | R | `payment_method`/`shipping_method` tanpa whitelist server-side saat simpan order | Checkout |
| F-23 | R | Penjual melihat seluruh isi order multi-toko (produk & alamat penjual lain) | Pesanan |
| F-24 | R | Relasi `OrderItem->product` untuk produk soft-deleted mengembalikan null (UI aman karena pakai snapshot) | Pesanan |
| F-25 | R | `Header` cart count = jumlah produk unik, bukan total kuantitas | UI |
| F-26 | R | Seeder demo (Product/Order/Category/SellerApplications) tidak dipanggil `DatabaseSeeder` | Ops |
| F-27 | R | `/dashboard` redirect ke `/admin` untuk semua role, termasuk buyer | Auth |
| F-28 | R | `docs/` tidak memuat alur stok/pembayaran; README masih menyebut PHP 8.2 (image 8.4) | Docs |

---

## Bukti yang dijalankan

| Verifikasi | Hasil |
|---|---|
| `GET /products?minRating=4` | **500** (`SQLSTATE[42703] column "rating" does not exist`) |
| Probe `ProductsPage::getFilteredProductsProperty()` dengan `sortBy=rating` & katalog kosong | tidak error — karena `paginate()` mengembalikan koleksi kosong tanpa menjalankan query fetch |
| Probe yang sama setelah menambah 1 produk `published` | **`SQLSTATE[42703] column "rating" does not exist`** (dan `sold` juga tidak ada) |
| `information_schema.columns` tabel `products` | 15 kolom, **tanpa `rating` dan `sold`** |
| `grep -rn "decrement\|increment(" app` | tidak ada pengurangan stok sama sekali |
| `php -r 'function_exists("imagejpeg")'` di container | `NO`; `imagecreatefromjpeg` `NO`; `imagecreatefrompng` `yes` |
| Probe `config('database.default')` saat test | `pgsql` + `cismart_db` (bukan sqlite) |
| Jumlah user dev sebelum/sesudah satu run `pest` | 1 → **0** (suite `migrate:fresh` di DB dev) |
| Suite penuh | 54 passed, 4 failed (2 × `imagejpeg`, 2 × `CreateShop` — sudah ada di `HEAD`) |

---

## Detail & saran perbaikan

### F-01 — Stok tidak pernah berkurang (K)
`app/Livewire/Pages/CheckoutPage.php::submitOrder()` membuat `Order` + `OrderItem` tanpa menyentuh `products.stock`, dan tidak ada pengecekan `stock >= quantity`. `ProductDetailPage::incrementQuantity()` hanya membatasi di UI. Akibat: satu unit stok bisa dijual berkali-kali; penjual harus menolak manual.

Perbaikan:
1. Transaksional di `submitOrder()`: `Product::whereKey($id)->lockForUpdate()->first()`, tolak bila `stock < quantity` atau `status != published`/toko tidak approved.
2. `decrement('stock', $qty)` setelah order item dibuat.
3. Kembalikan stok pada `cancelled`/`rejected` (butuh F-09/F-14 agar transisi status terjamin).
4. Tambah tabel `stock_movements` (siapa, kapan, sebab) untuk rekonsiliasi.

### F-02 — Sort rating/popular & filter rating rusak (K)
`ProductsPage::getFilteredProductsProperty()` memakai `orderBy('rating')`, `orderBy('sold')`, dan `where('rating','>=',…)`, sementara tabel `products` tidak punya kolom itu (rating hanya ada di `shops`; `sold` tidak ada di mana pun). Opsi ini juga tampil di UI (`sortOptions`), jadi ini jalur yang bisa diklik pengguna.

Perbaikan (pilih salah satu):
- Cepat: hapus opsi `popular`/`rating` + filter `minRating` dari UI dan kode (`ProductsPage` + blade).
- Benar: agregasi popularitas per toko/produk secara eksplisit (mis. `sold` dihitung dari `order_items` yang ordernya `delivered`, atau kolom `sold_count` yang di-update saat delivered), dan rating produk (lihat F-08).
Tambah test regresi yang **menyisipkan produk** sebelum menguji sort (kalau katalog kosong, bug ini tidak terlihat).

### F-03 — Harga pesanan dari session (K)
`ProductDetailPage::addToCart()` menyimpan `price`, `name`, `image`, `shop_name` ke `session('cart')`; `CheckoutPage` memakai nilai itu untuk `subtotal`/`total`/`OrderItem.price`. Tidak ada re-validasi produk saat `submitOrder()`.

Perbaikan: di `submitOrder()` ambil ulang `Product::whereIn('id', ...)->get()` (beserta `shop`), pakai `price` dari DB, dan bila harga berbeda dari yang dipegang pembeli → batalkan dengan pesan "harga berubah, silakan ulangi". Simpan juga `price_at_checkout` bila ingin audit.

### F-04 — Kuantitas melebihi stok (K)
`CartPage::updateQuantity()` hanya menolak ≤ 0; `ProductDetailPage::addToCart()` menambah `quantity` ke keranjang yang sudah ada tanpa batas. `stock === 0` dicek, tapi `quantity > stock` tidak.

Perbaikan: clamp di kedua tempat (`min($qty, $product->stock)`) + validasi ulang saat checkout (F-01).

### F-05 — Order lintas toko (K)
Keranjang boleh berisi beberapa toko; `checkoutShop()` mengembalikan `null` bila `shopIds->count() !== 1`. Efeknya: `shipping_cost = 0`, metode delivery & QRIS diblokir, `shop_whatsapp = null`, `OrderItem.shop_id` mengandalkan nilai session, dan **satu** `status` untuk order dari banyak penjual — penjual A tak bisa mengelola bagiannya sendiri, dan pendapatan per toko dihitung dari `order_items` (rawan salah saat status dishare).

Perbaikan: jadikan `orders` **per toko** (pecah otomatis menjadi N order saat checkout, nomor order per toko, ongkir & QRIS per toko), atau larang multi-toko di keranjang sejak awal (lebih murah, tapi batasi UX). Pilihan pertama lebih benar dan sekaligus menyelesaikan F-23.

### F-06 — Status pembayaran vs status order (K)
`OrderResource` menyediakan `Select::make('status')` bebas (pending→delivered) untuk admin **dan** penjual, tanpa memandang `payment_status`. Widget `AdminRevenueChart`/`AdminStatsOverview`/`SellerStatsOverview` menjumlahkan `total` untuk `status IN (confirmed, processing, shipped, delivered)` tanpa melihat pembayaran. Jadi order QRIS `unpaid` bisa masuk pendapatan.

Perbaikan:
1. State machine eksplisit (`pending → confirmed → processing → shipped → delivered`, plus `cancelled`) di model/service, dipakai oleh `OrderResource` (dropdown hanya berisi transisi sah).
2. `confirmed` hanya bila `payment_status = 'verified'` **atau** `payment_method = 'cod'`.
3. Predikat pendapatan: `payment_method='cod' AND status='delivered'` **atau** `payment_status='verified'` — jangan campur.
4. Untuk COD, tambah `payment_status` khusus (mis. `cod_collected`) saat diserahkan.

### F-07 — Ulasan tanpa transaksi selesai (T)
`User::hasOrderedFrom()` = "ada `order_items` di toko ini milik user", tanpa memfilter status/pembayaran. Order QRIS `unpaid` yang baru dibuat sudah cukup untuk memberi ulasan bintang 5 (dan hanya sekali seumur hidup per toko).

Perbaikan: syarat minimal `orders.status = 'delivered'` **dan** (`payment_status = 'verified'` atau `payment_method='cod'`). Tambahkan kemampuan edit/hapus ulasan + laporan, dan tampilkan "Ulasan terverifikasi".

### F-08 — Rating produk tidak pernah ada (T)
Review hanya terhubung ke `shops` (`Shop::refreshRating()`), sedangkan halaman produk menampilkan `$product->rating ?? 0` → selalu 0. Tidak ada rating/sold per produk.

Perbaikan: putuskan model ulasan: (a) sederhana — hapus UI rating produk dan pakai rating toko saja; (b) lengkap — `reviews` dengan `order_item_id` + agregat `products.rating/total_reviews/sold_count` yang di-update saat ulasan dibuat dan saat order `delivered`.

### F-09 — Order menggantung & tak bisa dibatalkan pembeli (T)
Tidak ada `expires_at` untuk QRIS `unpaid`, tidak ada job auto-cancel, tidak ada aksi batal/konfirmasi terima untuk pembeli (`OrdersPage` hanya daftar). `status='pending'` bisa bertahan selamanya dan (setelah F-01 diperbaiki) akan mengunci stok.

Perbaikan: `expires_at` (mis. 24 jam) + scheduled command untuk membatalkan & mengembalikan stok; aksi pembeli "Batalkan" (selama belum diproses) dan "Pesanan diterima" yang memindahkan ke `delivered` (memengaruhi F-07).

### F-10 — Tidak ada notifikasi (T)
`orders.shop_whatsapp` disimpan, `whatsapp_sent_at` tidak pernah diisi; `grep` tidak menemukan `Mail::`, `->notify(`, atau pengiriman WA. Pembeli dan penjual sama-sama tidak diberi tahu perubahan status; notifikasi yang ada hanya toast Livewire.

Perbaikan: `Notifiable` + notifikasi database untuk event kunci (order dibuat, bukti dibayar, pembayaran diverifikasi/ditolak, status berubah), lalu opsional email/WA Business API + isi `whatsapp_sent_at`.

### F-11 — Tanpa rate limit (T)
`routes/web.php` tidak memakai `throttle`, dan Livewire actions (login/register) tidak dibatasi. Login hanya memvalidasi format.

Perbaikan: `RateLimiter::for('login')` + middleware `throttle:login` pada route login/register; batasi juga `CheckoutPage::submitOrder` (idempotensi: kunci berdasarkan session/pending order).

### F-12 — Siklus hidup akun belum lengkap (T)
`email_verified_at` hanya diisi seeder, tidak ada middleware `verified`; tidak ada rute/komponen reset password; kolom `two_factor_*` tidak dipakai; `Auth::attempt(..., true)` memaksa remember-me; tidak ada ganti email/telepon verifikasi.

Perbaikan: aktifkan verifikasi email (atau tandai jelas bahwa email tidak diverifikasi), buat alur forgot/reset password dengan token Supabase/`password_reset_tokens` yang sudah ada, tambah opsi "ingat saya" yang benar-benar pilihan, dan hapus/implementasikan kolom 2FA.

### F-13 — Role seller diberikan sebelum approval (T)
`RegisterUmkmPage::submit()` memanggil `$user->assignRole('seller')` segera, sementara `User::canAccessPanel()` mengizinkan `seller` → pemohon `pending` bisa masuk `/admin`, membuat toko (status `pending` lewat `CreateShop`) dan produk draft. Sejak Opsi A, `provisionShop()` sudah menugaskan role saat approve.

Perbaikan: hapus `assignRole('seller')` di `RegisterUmkmPage` dan `SellerApplicationResource` (role hanya dari `provisionShop()`), lalu batasi `ShopResource::canCreate()`/`ProductResource::canCreate()` pada penjual yang permohonannya `approved` (tambahan cek di policy, bukan cuma UI).

### F-14 — Riwayat & integritas data pesanan (T)
Tidak ada tabel riwayat status; `confirmed_at`/`cancelled_at`/`whatsapp_sent_at` bisa diisi manual dari form tanpa mengubah `status`; `DeleteAction`/`DeleteBulkAction` ada untuk order (data transaksi) dan `Order` tidak memakai `SoftDeletes`.

Perbaikan: `order_status_histories` (from, to, actor, reason, at), isi timestamp otomatis di satu service transisi, dan `SoftDeletes` pada `orders` (hapus aksi delete permanen kecuali super admin).

### F-15 — Route debug ter-deploy (T)
`routes/web.php` memuat `/test-signed` (memvalidasi tanda tangan) dan `/make-test-url` (membuat signed URL 5 menit) untuk siapa pun. Ini sisa pengujian.

Perbaikan: hapus, atau bungkus dengan `if (app()->environment('local'))`.

### F-16 — ML service terbuka (S)
`ml_service/app.py`: `CORS(app)` tanpa origin, tidak ada token/API key; `docker-compose.yml` mempublish `5000:5000`. `/api/train` menerima unggahan siapa pun lalu **menimpa model** yang dipakai produksi. `/api/download/<filename>` memakai `os.path.join(DATA_DIR, filename)` (Flask `string` converter tidak menerima `/`, jadi traversal penuh tertahan, tapi tetap tidak ada whitelist/`secure_filename`).

Perbaikan: header `X-Internal-Token` yang diperiksa semua endpoint (kecuali `/health`), `CORS(app, origins=['http://laravel_app'])`, jangan publish port 5000 di produksi (hanya network internal), dan `send_from_directory` + whitelist nama file.

### F-17 — Training sinkron & model ditimpa (S)
`/api/train` melatih di dalam request (gunicorn `--timeout 300`, `--workers 2` tanpa shared lock). Dua worker dapat memuat/menulis `rf_best_model.joblib` bersamaan; request prediksi bisa membaca file yang sedang ditulis.

Perbaikan: jalankan training sebagai job terpisah (worker/thread + status polling), tulis model secara atomik (tmp file → `os.replace`), simpan versi model (`models/v1/…`) dan arahkan prediksi ke versi aktif yang tersimpan di DB/`current.json`.

### F-18 — Penumpukan file training (S)
Semua unggahan disimpan permanen ke volume `ml_data` dengan nama berbasis timestamp; tidak ada pembersihan.

Perbaikan: retensi (mis. simpan N terakhir / hapus setelah training sukses), atau simpan di object storage dengan masa simpan.

### F-19 — gd tanpa JPEG di image app (S)
`docker/php/Dockerfile` memasang `libpng-dev` tapi tidak `libjpeg-dev`; hasil `docker-php-ext-install gd` tanpa JPEG support. Terverifikasi di container: `imagejpeg` dan `imagecreatefromjpeg` tidak ada. Dampak: `QrisCheckoutTest` & `RegistrationTest` gagal, dan `QrisImageDecoder` (Zxing) gagal mendekode gambar QRIS berformat **JPEG** yang diunggah penjual di `ShopResource` — PNG jalan, jadi bug ini bisa muncul sporadis di produksi.

Perbaikan: tambahkan `libjpeg-dev` (dan `libfreetype6-dev` bila perlu) sebelum `docker-php-ext-install`, rebuild, lalu verifikasi `php -r 'var_dump(function_exists("imagecreatefromjpeg"));'`.

### F-20 — Test menjalankan `migrate:fresh` di DB dev (S)
`phpunit.xml` memakai `<env name="DB_CONNECTION" value="sqlite"/>` tanpa `force="true"`, sedangkan `docker-compose.yml` menyuntikkan `DB_CONNECTION=pgsql` ke environment container. PHPUnit tidak menimpa variabel yang sudah ada → `config('database.default')` tetap `pgsql`, `RefreshDatabase` tidak mengenalinya sebagai in-memory, dan menjalankan `migrate:fresh` sekali per run → **seluruh tabel DB dev dibuat ulang**. Terbukti: user dev 1 → 0 setelah satu run test.

Perbaikan: `force="true"` untuk semua `<env>` di `phpunit.xml`, atau hapus `DB_CONNECTION`/`DB_DATABASE` dari environment service `app` (biarkan `.env` yang menentukan) dan jalankan test dengan `DB_CONNECTION=sqlite`. Tambahan aman: tolak test bila `config('database.default') !== 'sqlite'` di `TestCase::setUp()`.

### F-21 — Tanpa CI (S)
Tidak ada workflow (memory menyebut `docker-vps.yml` yang tidak ada di repo). Semua regresi di atas hanya ketahuan lewat audit manual.

Perbaikan: job minimal — `pest`, `pint --test`, `npm run build`, `docker build` — plus pengecekan migrasi di DB bersih.

### F-22 — Integritas input order (R)
`paymentMethod`/`shippingMethod` adalah properti Livewire publik yang nilainya masuk langsung ke kolom string tanpa whitelist di `submitOrder()`. `submitPayment()` hanya menolak string kosong; `submitAddress()` membatasi `shippingMethod` ke `pickup|delivery`, tetapi `submitOrder()` bisa dipanggil tanpa melewati step itu.

Perbaikan: ulangi validasi (`in:pickup,delivery`, `in:qris,cod`) di awal `submitOrder()` dan turunkan biaya ongkir **hanya** dari `shippingMethod === 'delivery'` (sudah) + pastikan `delivery_latitude/longitude` null bila bukan delivery (sudah).

### F-23 — Visibilitas order multi-toko (R)
`OrderPolicy::view/update` mengizinkan penjual melihat order bila **ada satu** item miliknya. Pada order gabungan, penjual A melihat produk, harga, dan alamat pembeli milik penjual B. Setelah F-05 dipecah per toko, ini otomatis hilang.

### F-24 — `OrderItem->product` untuk produk soft-deleted (R)
`products` memakai soft delete, sedangkan FK `order_items.product_id` `nullOnDelete` hanya berlaku pada hard delete. Relasi `product()` default menyembunyikan model terhapus → `null`. Tampilan aman karena memakai snapshot `product_name`/`product_image`, tetapi jangan mengandalkan relasi ini untuk laporan.

Perbaikan: `->withTrashed()` pada relasi `product()`, atau sadari dan dokumentasikan.

### F-25 — Cart badge (R)
`Header` menghitung `count(session('cart'))` = jumlah baris produk, bukan total kuantitas. Ubah ke `array_sum(array_column($cart,'quantity'))` bila ingin akurat.

### F-26 — Seeder demo (R)
`DatabaseSeeder` hanya menjalankan `RolesAndPermissionsSeeder` + `AdminUserSeeder`; `CategorySeeder`, `ProductSeeder`, `OrderSeeder`, `SellerApplicationsSeeder` tidak dipanggil → aplikasi jalan dengan katalog kosong (dan justru itu yang menyembunyikan F-02). Pertimbangkan `--class=…` terpisah untuk demo/staging.

### F-27 — `/dashboard` (R)
`Route::redirect('/dashboard','/admin')` berlaku untuk semua role; buyer yang tersesat ke `/dashboard` akan mendapat 403. Arahkan buyer ke `/orders` atau `/`.

### F-28 — Dokumentasi (R)
`docs/` berisi panduan ML/deployment tetapi tidak ada dokumen alur (order/pembayaran/stok/ulasan). README masih menyebut PHP 8.2 sedangkan image 8.4; `docs/` tidak menyebut aturan retensi data.

---

## Urutan perbaikan yang disarankan

1. **Uang & stok (F-01, F-04, F-03, F-06)** — satu gelombang, karena saling terkait: re-validasi produk, kurangi stok dengan lock, state machine + guard pembayaran, predikat pendapatan.
2. **Struktur order (F-05, F-23, F-14)** — pecah per toko + riwayat status + soft delete.
3. **Kegagalan yang terlihat pengguna (F-02, F-08)** — sort/filter rusak dan rating produk tidak ada.
4. **Siklus pesanan & ulasan (F-09, F-07, F-10)**.
5. **Auth & keamanan (F-11, F-12, F-13, F-15, F-16, F-17)**.
6. **Fondasi ops/test (F-20, F-19, F-21, F-18, F-26)** — F-20 lebih dulu supaya test tidak lagi menghapus data dev.

---

# Status Perbaikan (2026-11-10, gelombang perbaikan menyeluruh)

Keputusan yang dipakai: order **dipecah per toko**, ulasan **per produk + kolom agregat**,
auth dengan **reset password + rate limit + verifikasi email wajib**.
Suite test: **94 passed, 0 failed** (sqlite in-memory; lihat `docs/ALUR-SISTEM.md`).

| ID | Status | Bukti / perubahan |
|-----|--------|-------------------|
| F-01 | ✅ | `CheckoutPage::submitOrder()` ambil produk `lockForUpdate()`, tolak bila stok kurang, `decrement('stock')`; `Order::restoreStock()` saat batal. Test: `CheckoutFlowTest`, `OrderLifecycleTest`. |
| F-02 | ✅ | Kolom `products.rating/total_reviews/sold_count` (migrasi `2026_11_10_000001`); sort `rating`/`popular` + filter `minRating` memakai kolom nyata. Test: `ProductSortingTest`. |
| F-03 | ✅ | Harga/ nama/ gambar disinkronkan dari DB (`syncCheckoutItemsWithDatabase`), `priceChanges()` membatalkan order bila harga berubah. Test: `CheckoutFlowTest`. |
| F-04 | ✅ | `addToCart()` & `updateQuantity()` dibatasi stok; produk tak dijual dibuang. |
| F-05 | ✅ | Satu order per toko (`orders.shop_id`, migrasi `000003`), ongkir & QRIS per toko, validasi "semua toko mendukung". Test: `CheckoutFlowTest`. |
| F-06 | ✅ | `Order::TRANSITIONS` + `transitionTo()`; `confirmed` butuh `payment_status=verified`/COD; widget memakai `Order::scopeRevenue()`. Test: `OrderLifecycleTest`. |
| F-07 | ✅ | `User::hasOrderedFrom()` & `hasDeliveredProduct()` mensyaratkan pesanan `delivered`. Test: `ReviewGatingTest`. |
| F-08 | ✅ | Ulasan punya `product_id`/`order_item_id`, form ulasan di halaman produk, `Product::refreshRating()`. Test: `ReviewGatingTest`. |
| F-09 | ✅ | `orders.expires_at`, perintah `orders:expire` (jadwal per jam), tombol Batal & Pesanan diterima. Test: `OrderLifecycleTest`. |
| F-10 | ✅ | `App\Notifications\OrderNotification` (database + mail) untuk placed/proof/verified/rejected/status; tabel `notifications`; daftar notifikasi di `/orders`; tautan `wa.me` ke penjual. |
| F-11 | ✅ | `RateLimiter` di login (5/5 mnt), register & permohonan UMKM (5/10 mnt), checkout (10/mnt), verifikasi email (3/5 mnt), lupa sandi (3/10 mnt). Test: `AuthFlowTest`. |
| F-12 | ✅ | Reset password (`/forgot-password`, `/reset-password/{token}`), `MustVerifyEmail` + `EnsureEmailVerified` (`REQUIRE_EMAIL_VERIFICATION`), "Ingat saya" opsional. Kolom `two_factor_*` tetap belum dipakai (2FA tidak dipilih). Test: `AuthFlowTest`. |
| F-13 | ✅ | Role `seller` hanya diberikan `provisionShop()`; policy `ShopPolicy`/`ProductPolicy` mensyaratkan permohonan approved. Test: `RegistrationTest`, `ShopApprovalTest`. |
| F-14 | ✅ | Tabel `order_status_histories` + RelationManager di panel; `confirmed_at`/`cancelled_at` otomatis & disabled di form; `orders` memakai SoftDeletes. |
| F-15 | ✅ | `/test-signed` & `/make-test-url` hanya di `app()->environment('local')`. Test: `AuthFlowTest`. |
| F-16 | ✅ | `X-Internal-Token` di semua endpoint (kecuali `/health`), CORS dibatasi `CORS_ORIGINS`, port ML dipublish ke `127.0.0.1`, download pakai `secure_filename` + `send_from_directory`. |
| F-17 | ✅ | `/api/train` → `202` + `job_id`, status di `GET /api/train/{job_id}`, `TRAINING_LOCK`, penyimpanan model atomik (`_atomic_dump`). |
| F-18 | ✅ | `cleanup_training_files()` menyisakan `ML_MAX_TRAINING_FILES` (default 10) file terakhir. |
| F-19 | ✅ | Dockerfile: `libjpeg-dev` + `docker-php-ext-configure gd --with-jpeg`; diverifikasi `imagejpeg`/`imagecreatefromjpeg` = yes. |
| F-20 | ✅ | `phpunit.xml` `force="true"` **dan** `tests/TestCase.php` memaksa `$_SERVER`/`$_ENV` + guard menolak koneksi non-sqlite. |
| F-21 | ✅ | `.github/workflows/ci.yml`: job Pest (sqlite) + Vite build. |
| F-22 | ✅ | Whitelist `paymentMethod`/`shippingMethod` di `submitPayment()`/`submitOrder()`. |
| F-23 | ✅ | `OrderPolicy` memakai `orders.shop_id`; `OrderResource::getEloquentQuery()`/badge fokus per toko. |
| F-24 | ✅ | `OrderItem::product()` memakai `withTrashed()`. |
| F-25 | ✅ | Badge keranjang = total kuantitas. |
| F-26 | ✅ | `DemoSeeder` (kategori/permohonan/produk/order) dipisah dari `DatabaseSeeder`. |
| F-27 | ✅ | `/dashboard` mengarahkan admin/penjual ke `/admin`, pembeli ke `/orders`. |
| F-28 | ✅ | Dokumentasi baru `docs/ALUR-SISTEM.md`; README diperbarui. |

## Catatan sisa (belum dikerjakan / keputusan terbuka)

- **2FA** belum diimplementasikan; kolom `two_factor_*` masih ada tetapi tidak terpakai.
- **Verifikasi email wajib** dengan `MAIL_MAILER=log` berarti tautan verifikasi hanya
  muncul di `storage/logs/laravel.log`. Untuk demo tanpa mail catcher set
  `REQUIRE_EMAIL_VERIFICATION=false`.
- **WhatsApp** belum dikirim otomatis (hanya tautan `wa.me`); `whatsapp_sent_at`
  sekarang tidak lagi diedit manual dari panel.
- `seller_applications.business_type` **belum** dipetakan ke kolom/kategori toko
  (masih temuan terbuka dari audit sebelumnya).
