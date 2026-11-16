# Slice 01-p0 — Halaman legal yang bisa dibaca publik

## Objective

Pengunjung tanpa akun bisa membuka `/syarat-ketentuan` dan `/perjanjian-penjual`, membaca versi serta tanggal berlaku yang datang dari config, dan berpindah antara keduanya lewat tautan di footer yang hari ini masih `<button>` mati.

Open question yang diwariskan (dari spec §17, tidak menghambat): Q-001 "Perlukah Kebijakan Privasi masuk plan ini karena footer sudah menampilkannya?" — default: tidak, dibiarkan tombol mati, dibuat di plan terpisah. Q-004 "Apakah teks legal perlu ditinjau ahli hukum sebelum rilis?" — default: ya, tapi tidak menahan implementasi; versi 1.0 dirilis sebagai draft.

## Vertical Slice

Route → config → Blade → footer; satu pengunjung bisa membaca dokumen legal lengkap tanpa login. Satisfies §8 FR-001, FR-002, FR-003, FR-004, FR-005, FR-006, FR-007, FR-022; §7 US-001, US-002 (bagian "tahu apa yang saya setujui"); §10 flow A.

## Tracer Bullet

Ini tracer bullet plan ini: jalur terpendek yang tetap end-to-end (request HTTP → route → data config → render), nol query database, dan tanpa menyentuh permohonan yang sudah ada. Ia juga menyiapkan URL yang akan dirujuk checkbox persetujuan di `02-p1`, sesuatu yang tidak bisa dibangun sebelum halaman ini ada.

## Dependencies

### BLOCKED BY

- None

### BLOCKS

- p1

## Tasks

### TASK-001 — Halaman Syarat & Ketentuan dari config

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

Buat `config/legal.php` (`terms` dan `seller_agreement`, masing-masing `version` + `effective_at`), daftarkan `GET /syarat-ketentuan` sebagai `legal.terms`, dan tulis view Blade statis yang memakai layout `components.layouts.app`. Isi S&K dibagi menjadi sekurangnya delapan pasal bernomor dengan `id` anchor stabil, plus daftar isi yang menautkan anchor tersebut; badge versi dan tanggal berlaku dibaca dari config, bukan diketik di view.

#### Acceptance Criteria

- `GET /syarat-ketentuan` mengembalikan 200 untuk tamu dan memakai header + footer aplikasi.
- Halaman menampilkan `config('legal.terms.version')` dan `effective_at` yang sudah diformat dalam bahasa Indonesia.
- Halaman memuat ≥ 8 pasal dengan `id` anchor, dan setiap tautan daftar isi menunjuk ke salah satu `id` itu.
- Mengubah `legal.terms.version` di config mengubah teks versi di halaman tanpa menyentuh Blade.

#### Dependencies

- None

### TASK-002 — Halaman Perjanjian Penjual

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

Daftarkan `GET /perjanjian-penjual` sebagai `legal.seller-agreement` dan tulis view-nya: para pihak, kewajiban penjual, komisi/pencairan, larangan, sanksi, penyelesaian sengketa, versi + tanggal berlaku dari `config('legal.seller_agreement')`. Tambahkan tautan silang dua arah dengan halaman S&K.

#### Acceptance Criteria

- `GET /perjanjian-penjual` mengembalikan 200 untuk tamu dan menampilkan versi kontrak dari config.
- Halaman menautkan ke `route('legal.terms')`, dan halaman S&K menautkan ke `route('legal.seller-agreement')`.
- Tautan luar/dokumen memakai `target="_blank"` hanya bila disertai `rel="noopener noreferrer"`.

#### Dependencies

- TASK-001

### TASK-003 — Footer menautkan halaman S&K

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

Ganti `<button>` "Syarat & Ketentuan" di `livewire/components/footer.blade.php` menjadi anchor ke `route('legal.terms')` dengan gaya yang sama seperti tautan footer lain. Tombol "Kebijakan Privasi", "Bantuan & FAQ", dan "Cara Berjualan" tidak diubah (non-goal §4).

#### Acceptance Criteria

- Halaman beranda merender tautan "Syarat & Ketentuan" dengan `href` sama dengan `route('legal.terms')`.
- Tautan itu bukan `<button>` lagi dan tetap tampil di footer setiap halaman.

#### Dependencies

- TASK-001