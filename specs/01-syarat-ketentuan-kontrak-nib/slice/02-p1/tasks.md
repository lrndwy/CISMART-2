# Slice 02-p1 — Persetujuan kontrak yang tercatat

## Objective

Calon penjual tidak bisa lagi mengirim permohonan tanpa menyetujui Perjanjian Penjual, dan setiap persetujuan meninggalkan satu baris audit (user, versi, IP, user-agent, waktu) yang tidak bisa tertimpa oleh pengajuan ulang.

Open question yang diwariskan (dari spec §17): Q-003 "Apakah penjual yang sudah `approved` sebelum plan ini wajib menyetujui kontrak (backfill consent)?" — default: tidak, hanya saat pengajuan berikutnya. Q-005 "Apakah admin perlu melihat riwayat persetujuan per versi di panel?" — default: tidak di plan ini, datanya sudah tersimpan.

Catatan eksekusi (2026-11-16): dua test lama di `tests/Feature/RegistrationTest.php` mengirim form permohonan tanpa field wajib baru sehingga gagal — keduanya diberi `nib_document` dan `agreement_accepted` tanpa mengubah assertion apa pun. Spec plan-01 §16 menulis "suite Pest harus tetap hijau tanpa perubahan pada test lama"; constraint itu terlalu ketat untuk field wajib baru dan dicatat di sini, bukan dengan mengedit spec (spec bersifat historis).

## Vertical Slice

Form Livewire → validasi → transaksi database → kartu status; satu calon penjual bisa mengirim permohonan dan admin bisa membuktikan persetujuannya. Satisfies §8 FR-008, FR-009, FR-010, FR-011, FR-012, FR-013, FR-020, FR-021; §7 US-002, US-004, US-006; §10 flow B (cabang persetujuan).

## Tracer Bullet

Bukan tracer bullet — jalur ini menunggu halaman legal dari `p0` karena label checkbox harus menautkan kontrak yang sudah ada.

## Dependencies

### BLOCKED BY

- p0

### BLOCKS

- p2

## Tasks

### TASK-004 — Skema: tabel persetujuan + kolom NIB

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

Satu migration baru (timestamp lebih baru dari `2025_09_25_124541`) yang membuat tabel `agreement_acceptances` (`user_id` FK cascade, `document_type` string 50, `version` string 20, `ip_address` string 45, `user_agent` text nullable, `accepted_at` timestamp, tanpa timestamps, unique `(user_id, document_type, version)`, index `(document_type, version)`) dan menambah `seller_applications.nib_document_path` (string nullable). Tambahkan model `AgreementAcceptance` dengan cast `accepted_at` ke datetime dan `$fillable` yang tepat. Tidak ada backfill.

#### Acceptance Criteria

- `php artisan migrate` berhasil pada database yang sudah berisi permohonan lama, dan `nib_document_path` baris lama bernilai `null`.
- Insert dua baris dengan `user_id` + `document_type` + `version` yang sama ditolak oleh unique index.
- `php artisan migrate:rollback --step=1` menghapus tabel dan kolomnya tanpa galat.

#### Dependencies

- None

### TASK-005 — Pencatatan persetujuan yang idempoten

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

Tambahkan `AgreementAcceptance::record(User $user, string $type, string $version, Request $request)` yang memakai `firstOrCreate` sehingga submit berulang pada versi yang sama tidak membuat baris kedua, serta `User::agreementAcceptances()` dan `User::hasAcceptedDocument(string $type, string $version): bool`. Versi baru menghasilkan baris baru tanpa mengubah baris lama.

#### Acceptance Criteria

- Memanggil `record()` dua kali dengan versi sama menyisakan tepat satu baris.
- Memanggil `record()` setelah versi naik membuat baris kedua dan baris pertama tidak berubah (`version` dan `accepted_at` tetap).
- Baris menyimpan `ip_address` dari request dan `accepted_at` terisi waktu server.

#### Dependencies

- TASK-004

### TASK-006 — Checkbox persetujuan di form permohonan

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

Di `App\Livewire\Pages\RegisterUmkmPage`: properti `agreement_accepted` (bool, default false), aturan `accepted` yang hanya berlaku bila `hasAcceptedDocument('seller_agreement', config('legal.seller_agreement.version'))` bernilai false, pesan galat Indonesia yang menyebut versi, dan label checkbox yang memuat tautan `route('legal.seller-agreement')` (`target="_blank" rel="noopener noreferrer"`). Bila versi aktif sudah disetujui, tampilkan keterangan tanggal persetujuan menggantikan checkbox. Pencatatan persetujuan terjadi di transaksi yang sama dengan pembuatan/pembaruan permohonan, setelah user tamu dibuat.

#### Acceptance Criteria

- Submit tanpa `agreement_accepted` menghasilkan galat validasi pada `agreement_accepted`, dan tidak ada baris `seller_applications` maupun `agreement_acceptances` baru.
- Submit dengan centang membuat permohonan `pending` dan tepat satu baris persetujuan dengan versi dari config.
- User yang sudah punya baris untuk versi aktif bisa submit ulang tanpa centang, dan tidak muncul baris persetujuan kedua.
- Label checkbox menampilkan versi dan tautan ke halaman Perjanjian Penjual.

#### Dependencies

- TASK-005