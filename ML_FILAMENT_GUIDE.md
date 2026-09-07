# Panduan Filament Admin - ML Prediction UMKM

## ✅ Status: READY TO USE

**Update:** 21 November 2025 - Error database constraint sudah diperbaiki!

---

## 📍 Lokasi Menu

Setelah login ke admin panel (`/admin`), Anda akan menemukan menu baru:

```
Machine Learning
└── Prediksi UMKM  (badge: jumlah prediksi 7 hari terakhir)
```

## 🎯 Fitur Utama

### 1. **List Prediksi** (`/admin/ml-predictions`)

Menampilkan tabel semua prediksi dengan kolom:

- ID
- Toko (jika terkait dengan shop)
- User (pembuat prediksi)
- Omzet
- Jumlah Tenaga Kerja
- Skala Usaha (badge: Mikro/Kecil/Menengah)
- Cluster (badge: A/B/C)
- Confidence (persentase)
- Tipe (Single/Batch)
- Tanggal dibuat

**Filter tersedia:**

- Cluster (A/B/C)
- Skala Usaha
- Tipe Prediksi

**Hak Akses:**

- **Admin**: Melihat semua prediksi
- **Seller/User**: Hanya melihat prediksi sendiri

---

### 2. **Buat Prediksi Baru** (`/admin/ml-predictions/create`)

Form input dengan 4 section:

#### **Section 1: Informasi Dasar**

- Toko (optional) - dropdown toko yang dimiliki
- Tipe Prediksi - Single/Batch
- Catatan

#### **Section 2: Data Keuangan**

Input dalam Rupiah (9 field):

- Omzet (required)
- Total Aset (required)
- Modal Kerja (required)
- Jumlah Investasi (required)
- Bangunan/Gedung
- Mesin/Peralatan
- Mesin/Peralatan Impor
- Pembelian/Pematangan Tanah
- Lain-lain

#### **Section 3: Data Operasional**

- Jumlah Tenaga Kerja (required, minimal 1)
- TKI (default 0)
- Jenis Perusahaan (dropdown: PT/CV/UD/Perorangan/Koperasi/Firma)
- Risiko Proyek (dropdown: Rendah/Sedang/Tinggi)
- Skala Usaha (dropdown: Mikro/Kecil/Menengah)
- Status Penanaman Modal (dropdown: PMDN/PMA/Non-Fasilitas)

#### **Section 4: Lokasi & Sektor**

- Kecamatan Usaha (required)
- Kelurahan Usaha (required)
- Sektor Pembina (dropdown: Pangan/Sandang/Kimia & Bahan Bangunan/Logam & Mesin/Aneka)
- Judul KBLI (required)

#### **Section 5: Hasil Prediksi** (collapsed, auto-fill)

- Cluster Prediksi (A/B/C)
- Confidence Score
- Model Version

**⚠️ Catatan Penting:**

- Saat submit, sistem akan otomatis mencoba prediksi via ML Service
- Jika ML Service offline/belum training → data tetap tersimpan dengan cluster null
- Jika ML Service sukses → hasil prediksi otomatis diisi

---

### 3. **Lihat Detail** (`/admin/ml-predictions/{id}`)

Tampilan read-only semua data prediksi yang sudah disimpan.

**Action buttons:**

- **Ubah** - Edit data prediksi

---

### 4. **Edit Prediksi** (`/admin/ml-predictions/{id}/edit`)

Form sama dengan Create, bisa mengedit semua field.

**Action buttons:**

- **Lihat** - Kembali ke view mode
- **Hapus** - (hanya admin) Delete prediksi

**⚠️ Note:**

- Edit tidak akan re-run ML prediction otomatis
- Untuk re-prediksi, Anda bisa:
  1. Edit field yang diinginkan
  2. Hapus cluster/confidence lama
  3. Save → sistem akan coba prediksi ulang

---

## 🔐 Hak Akses

### Admin

- ✅ Lihat semua prediksi (semua user)
- ✅ Buat prediksi baru
- ✅ Edit semua prediksi
- ✅ Hapus semua prediksi

### Seller/User

- ✅ Lihat prediksi sendiri saja
- ✅ Buat prediksi baru
- ✅ Edit prediksi sendiri
- ❌ Hapus prediksi (hanya admin)

---

## 🤖 Integrasi ML Service

### Status ML Service

1. **ML Service ONLINE + Model TRAINED** ✅
   - Hasil prediksi otomatis terisi saat create
   - Cluster, confidence, probabilities tersedia
2. **ML Service OFFLINE** ⚠️
   - Data tetap tersimpan
   - Cluster = null, confidence = null
   - Catatan otomatis ditambahkan: "[Warning] ML Service unavailable"
3. **ML Service ONLINE + Model NOT TRAINED** ⚠️
   - Sama seperti offline
   - Perlu training model dulu via API:
     ```bash
     curl -X POST http://localhost:5000/api/train \
       -F "file=@data_umkm.xlsx"
     ```

### Manual Prediction

Jika ML Service offline saat create, Anda bisa prediksi manual nanti:

1. Akses ML Service API:

   ```php
   // Via Laravel Tinker
   $ml = app(\App\Services\MLService::class);
   $prediction = \App\Models\MlPrediction::find(1);

   $result = $ml->predictSingle([
       'omzet' => $prediction->omzet,
       'aset' => $prediction->aset,
       // ... semua field
   ]);

   $prediction->update([
       'predicted_cluster' => $result['data']['prediction']['predicted_cluster'],
       'confidence' => $result['data']['prediction']['confidence'],
       'probabilities' => $result['data']['prediction']['probabilities'],
   ]);
   ```

2. Atau via cURL:
   ```bash
   curl -X POST http://localhost:5000/api/predict \
     -H "Content-Type: application/json" \
     -d @prediction_data.json
   ```

---

## 📊 Interpretasi Hasil

### Cluster Prediksi

- **Cluster A - UMKM Skala Kecil**
  - Omzet rendah
  - Tenaga kerja sedikit
  - Aset terbatas
- **Cluster B - UMKM Skala Menengah**
  - Omzet menengah (500jt - 5M)
  - Tenaga kerja 20-50 orang
  - Aset berkembang
- **Cluster C - UMKM Skala Besar**
  - Omzet tinggi (>5M)
  - Tenaga kerja banyak (>50 orang)
  - Aset besar

### Confidence Score

- **>90%**: Sangat yakin, prediksi akurat
- **70-90%**: Cukup yakin, prediksi reliable
- **50-70%**: Kurang yakin, data borderline
- **<50%**: Tidak yakin, perlu validasi manual

---

## 🚀 Tips Penggunaan

1. **Isi Data Lengkap**

   - Semakin lengkap data, semakin akurat prediksi
   - Field required harus diisi

2. **Training Model**

   - Upload data UMKM minimal 50 baris untuk training
   - Gunakan data Excel/CSV dengan 19 feature lengkap

3. **Bulk Prediction**

   - Untuk prediksi banyak data sekaligus
   - Upload Excel/CSV via ML Service API
   - Set `prediction_type = 'batch'`

4. **Monitor Badge**
   - Badge di sidebar = jumlah prediksi 7 hari terakhir
   - Berguna untuk tracking aktivitas

---

## 🐛 Troubleshooting

### "ML Service unavailable" saat create

**Solusi:**

1. Check ML service running: `docker ps | grep ml_service`
2. Check health: `curl http://localhost:5000/health`
3. Restart ML service: `docker compose restart ml_service`

### Prediksi tidak akurat

**Solusi:**

1. Check model info: `curl http://localhost:5000/api/model-info`
2. Re-training dengan data lebih banyak
3. Validasi input data (typo, nilai ekstrem)

### Tidak muncul di sidebar

**Solusi:**

1. Clear cache: `php artisan optimize:clear`
2. Cache Filament: `php artisan filament:cache`
3. Check user role: harus punya role `admin` atau `seller`

---

## 📝 Contoh Workflow

### Workflow 1: Admin Membuat Prediksi Manual

1. Login admin panel `/admin`
2. Klik **Machine Learning** → **Prediksi UMKM**
3. Klik **Buat Prediksi Baru**
4. Isi form (19 field + info dasar)
5. Klik **Create**
6. ✅ Prediksi tersimpan dengan hasil cluster otomatis
7. Bisa lihat detail atau edit

### Workflow 2: Seller Tracking UMKM Sendiri

1. Login sebagai seller
2. Hanya melihat prediksi untuk shop sendiri
3. Buat prediksi untuk toko yang dimiliki
4. Monitoring growth dari cluster A → B → C

### Workflow 3: Batch Prediction (via API)

1. Siapkan Excel dengan banyak UMKM data
2. Upload via API:
   ```bash
   curl -X POST http://localhost:5000/api/predict-batch \
     -F "file=@umkm_batch.xlsx"
   ```
3. Import hasil ke database via Laravel command
4. Lihat hasil di admin panel

---

## 🔗 Link Terkait

- **ML Service API Docs**: `http://localhost:5000/` (setelah deployment)
- **ML Integration Guide**: `ML_INTEGRATION_GUIDE.md`
- **ML Testing Guide**: `ML_TESTING_GUIDE.md`
- **Database Schema**: `database/migrations/*_create_ml_predictions_table.php`

---

## 📞 Support

Jika ada masalah atau pertanyaan:

1. Check dokumentasi lengkap di `ML_INTEGRATION_GUIDE.md`
2. Check test script: `php test_ml_prediction.php`
3. Review logs: `docker compose logs ml_service`
