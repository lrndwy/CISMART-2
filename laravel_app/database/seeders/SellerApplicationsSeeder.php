<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SellerApplication;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SellerApplicationsSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil user yang dibuat di RolesAndPermissionsSeeder, atau buat fallback demo user
        $admin = User::firstOrCreate(
            ['email' => 'admin@cismart.test'],
            [
                'name' => 'Admin CISMART',
                'password' => bcrypt('password'),
                'phone' => '+6281234567890',
                'address' => 'Jl. Admin No. 1, Cilacap',
                'email_verified_at' => now(),
            ]
        );

        $buyer1 = User::firstOrCreate(
            ['email' => 'buyer1@cismart.test'],
            [
                'name' => 'Andi Pembeli',
                'password' => bcrypt('password'),
                'phone' => '+6281234567893',
                'address' => 'Jl. Konsumen No. 20, Jakarta',
                'email_verified_at' => now(),
            ]
        );

        $buyer2 = User::firstOrCreate(
            ['email' => 'buyer2@cismart.test'],
            [
                'name' => 'Rina Shopper',
                'password' => bcrypt('password'),
                'phone' => '+6281234567894',
                'address' => 'Jl. Belanja No. 25, Bandung',
                'email_verified_at' => now(),
            ]
        );

        $seller1 = User::firstOrCreate(
            ['email' => 'seller1@cismart.test'],
            [
                'name' => 'Sari Handmade',
                'password' => bcrypt('password'),
                'phone' => '+6281234567891',
                'address' => 'Jl. Kerajinan No. 10, Cilacap',
                'email_verified_at' => now(),
            ]
        );

        // Hapus seluruh data demo lama (opsional) agar idempotent saat seeding ulang
        // Comment / hapus jika tidak diinginkan
        // SellerApplication::truncate();

        // 1) Pending application (buyer1)
        SellerApplication::firstOrCreate(
            [
                'user_id' => $buyer1->id,
                'business_name' => 'Warung Andi'
            ],
            [
                'business_description' => 'Warung sederhana menjual aneka makanan ringan dan minuman tradisional.',
                'business_type' => 'food',
                'business_address' => 'Jl. Konsumen No. 20, Jakarta',
                'business_phone' => '+6281234567893',
                'business_email' => $buyer1->email,
                'business_documents' => [
                    'ktp' => 'documents/andi_ktp.jpg',
                    'siup' => null,
                ],
                'status' => 'pending',
            ]
        );

        // 2) Approved application (buyer2) - disetujui oleh admin
        SellerApplication::firstOrCreate(
            [
                'user_id' => $buyer2->id,
                'business_name' => "Rina's Crafts"
            ],
            [
                'business_description' => 'Kerajinan tangan dari bahan lokal (tas, aksesori, suvenir).',
                'business_type' => 'craft',
                'business_address' => 'Jl. Belanja No. 25, Bandung',
                'business_phone' => '+6281234567894',
                'business_email' => $buyer2->email,
                'business_documents' => [
                    'ktp' => 'documents/rina_ktp.jpg',
                    'siup' => 'documents/rina_siup.pdf',
                    'portfolio' => ['images/port1.jpg', 'images/port2.jpg'],
                ],
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]
        );

        // 3) Rejected application (new demo user)
        $applicant = User::firstOrCreate(
            ['email' => 'applicant1@cismart.test'],
            [
                'name' => 'Dedi Calon',
                'password' => bcrypt('password'),
                'phone' => '+6281111111111',
                'address' => 'Jl. Contoh No. 1, Cilacap',
                'email_verified_at' => now(),
            ]
        );

        SellerApplication::firstOrCreate(
            [
                'user_id' => $applicant->id,
                'business_name' => 'Souvenir Dedi'
            ],
            [
                'business_description' => 'Pusat souvenir kecil-kecilan untuk wisatawan.',
                'business_type' => 'souvenir',
                'business_address' => 'Jl. Contoh No. 1, Cilacap',
                'business_phone' => '+6281111111111',
                'business_email' => $applicant->email,
                'business_documents' => [
                    'ktp' => 'documents/dedi_ktp.jpg',
                ],
                'status' => 'rejected',
                'rejection_reason' => 'Dokumen SIUP tidak lengkap dan alamat tidak jelas.',
            ]
        );

        // 4) Pending application from an existing seller (edge case demo)
        SellerApplication::firstOrCreate(
            [
                'user_id' => $seller1->id,
                'business_name' => 'Sari Handmade - Cabang'
            ],
            [
                'business_description' => 'Ekspansi cabang kecil Sari Handmade.',
                'business_type' => 'craft',
                'business_address' => 'Jl. Kerajinan No. 11, Cilacap',
                'business_phone' => '+6281234500000',
                'business_email' => $seller1->email,
                'business_documents' => [
                    'ktp' => 'documents/sari_ktp.jpg',
                    'siup' => 'documents/sari_siup.pdf',
                ],
                'status' => 'pending',
            ]
        );
    }
}
