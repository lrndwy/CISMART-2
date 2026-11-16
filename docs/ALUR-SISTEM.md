# Alur Sistem CISMART

Ringkasan alur utama beserta aturan (invariant) yang **ditegakkan di server**.
Dokumen ini menggantikan asumsi yang sebelumnya hanya ada di UI.

## 1. Akun & login

1. **Pembeli**: `/register` → role `buyer` → email verifikasi terkirim.
2. **UMKM**: `/register/umkm` → akun (role `buyer`) + `seller_applications` status `pending`.
   Role `seller` **belum** diberikan di tahap ini.
3. Admin menyetujui di **Panel ▸ Permohonan Penjual ▸ Approve**:
   `SellerApplication::provisionShop()` → assign role `seller` + buat `Shop`
   (`status=approved`, slug unik) dari data permohonan.
   Jika penjual sudah lebih dulu membuat toko, datanya tidak ditimpa — hanya
   field kosong yang dilengkapi.
4. Login: dibatasi 5 percobaan / 5 menit per email+IP. "Ingat saya" opsional.
5. Lupa sandi: `/forgot-password` → tautan `/reset-password/{token}` (rate limit 3 / 10 menit).
6. Verifikasi email: `REQUIRE_EMAIL_VERIFICATION=true` (default) → area akun
   (`/checkout`, `/orders`, `/profile`) hanya terbuka setelah verifikasi.
   Set `false` untuk demo lokal tanpa mail catcher.

## 2. Toko & produk

- Satu penjual = satu toko. `ShopResource::canCreate()` hanya `true` untuk admin,
  atau penjual dengan permohonan **disetujui** yang belum punya toko.
- `ShopPolicy::create()` / `ProductPolicy::create()` menegakkan hal yang sama di
  lapisan policy (bukan hanya UI).
- Produk tampil ke pembeli bila `products.status = published` **dan** tokonya
  `approved` (`Product::scopePublished`).
- Penjual melihat hanya toko/produk/pesanan miliknya (`getEloquentQuery`, policy).

## 3. Keranjang

- Keranjang disimpan di session dan **bukan** sumber harga.
- Kuantitas selalu dibatasi stok saat disimpan (`ProductDetailPage::addToCart`,
  `CartPage::updateQuantity`); produk yang sudah tidak dijual dibuang otomatis.

## 4. Checkout (order per toko)

`CheckoutPage::submitOrder()`:

1. Semua produk diambil ulang dari DB (`published` + toko `approved`).
2. Harga dibandingkan dengan snapshot keranjang. Bila berbeda → checkout
   dibatalkan dan keranjang disinkronkan ulang ("harga berubah").
3. Pembayaran/pengiriman divalidasi whitelist (`cod|qris`, `pickup|delivery`) dan
   harus didukung **semua** toko pada checkout tersebut.
4. Item dikelompokkan **per toko**; satu `orders` dibuat untuk tiap toko
   (`orders.shop_id`), dengan subtotal dan ongkir masing-masing.
5. Stok dikurangi `lockForUpdate()` di dalam transaksi; bila stok kurang →
   seluruh transaksi dibatalkan.
6. QRIS: `qris_payload` dibuat per order (dynamic bila `qris_mode=dynamic`),
   `payment_status=unpaid`. COD: `payment_status=not_required`.
7. Keranjang baru dibersihkan setelah transaksi sukses.

## 5. Siklus hidup pesanan

Status dan transisi yang sah (`Order::TRANSITIONS`):

```
pending → confirmed → processing → shipped → delivered
   ↓          ↓            ↓
cancelled  cancelled    cancelled
```

- Perubahan status **hanya** lewat `Order::transitionTo()`: menulis
  `order_status_histories`, mengisi `confirmed_at`/`cancelled_at`, mengembalikan
  stok saat `cancelled`, menambah/mengurangi `products.sold_count`, dan mengirim
  notifikasi.
- `confirmed` hanya boleh bila `payment_status=verified` **atau** metode `cod`.
- Admin/penjual tidak bisa melompati status dari panel; dropdown hanya berisi
  transisi yang sah.
- Pembeli: **Batalkan** (selama `pending`/`confirmed` dan belum terverifikasi),
  **Pesanan diterima** (dari `shipped` → `delivered`) di `/orders`.
- Notifikasi dibuat untuk `placed`, `proof_uploaded`, `payment_verified`,
  `payment_rejected`, `status_changed` (channel database + mail).
- `orders:expire` (dijadwalkan tiap jam) membatalkan pesanan QRIS yang lewat
  `expires_at` (`ORDER_PAYMENT_WINDOW_HOURS`, default 24 jam) dan COD yang tak
  pernah dikonfirmasi (`ORDER_CONFIRMATION_WINDOW_HOURS`, default 72 jam),
  sekaligus mengembalikan stok.

## 6. Pembayaran

- Bukti diunggah pembeli di `/orders/{order}/pay` → `payment_status=awaiting_verification`
  → notifikasi ke penjual.
- Penjual/admin memverifikasi (`verifyPayment`, otomatis `confirmed`) atau menolak
  dengan catatan (`rejectPayment`).
- **Pendapatan** dihitung dengan `Order::scopeRevenue()`:
  QRIS yang `verified`, atau COD yang sudah `delivered`. Status saja tidak cukup.

## 7. Ulasan & rating

- Ulasan produk (`ProductDetailPage`) hanya untuk pembeli dengan pesanan
  `delivered` berisi produk itu; satu ulasan per pengguna per produk.
- Ulasan toko (`ShopDetailPage`) hanya untuk pembeli yang pernah menerima
  pesanan dari toko tersebut.
- `products.rating`, `products.total_reviews`, `products.sold_count` dan
  `shops.rating`/`total_reviews` dihitung ulang otomatis (`refreshRating()`),
  sehingga sort "Rating Tertinggi"/"Terpopuler" dan filter rating bekerja.

## 8. ML service (Flask)

- Endpoint selain `/health` memerlukan header `X-Internal-Token` = `ML_SERVICE_TOKEN`.
- `/api/train` mengembalikan `202` + `job_id`; pantau di `GET /api/train/{job_id}`
  (training berjalan di thread, diserialkan oleh `TRAINING_LOCK`).
- Model disimpan atomik (file `.tmp` → `os.replace`) agar prediksi tidak membaca
  model setengah tertulis.
- File training lama dibatasi `ML_MAX_TRAINING_FILES` (default 10).
- Port 5000 hanya di-publish ke `127.0.0.1`.

## Menjalankan test

```bash
docker compose exec app composer install      # image produksi tanpa dev-deps
docker compose exec app ./vendor/bin/pest
```

Test dipaksa berjalan di **sqlite in-memory** (`tests/TestCase.php` memaksa
`$_SERVER`/`$_ENV`, karena docker-compose menyuntik `DB_CONNECTION=pgsql`).
`TestCase` akan menolak jalan bila koneksi bukan sqlite, supaya
`RefreshDatabase` tidak pernah menghapus data dev.
