<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\SellerApplication;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = User::role('seller')->with('shops')->get();

        if ($sellers->isEmpty()) {
            $this->command->warn('No sellers found. Run RolesAndPermissionsSeeder first.');
            return;
        }

        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->command->info('No categories found — please run CategorySeeder or create categories first.');
            return;
        }

        foreach ($sellers as $seller) {
            $shop = $seller->shops()->first();

            if (!$shop) {
                // coba ambil data dari seller_applications jika ada
                $app = SellerApplication::where('user_id', $seller->id)->first();
                if ($app) {
                    $shopName = $app->business_name;
                    $shopDesc = $app->business_description;
                } else {
                    $shopName = $seller->name . ' Shop';
                    $shopDesc = 'Toko demo dibuat oleh seeder';
                }

                $shop = Shop::create([
                    'user_id' => $seller->id,
                    'name' => $shopName,
                    'slug' => Str::slug($shopName) . '-' . Str::random(5),
                    'description' => $shopDesc,
                ]);

                $this->command->info("No shop found for seller {$seller->email} — created shop '{$shop->name}'.");
            }

            // buat 4-10 produk per shop, pilih kategori random dari existing categories
            $count = fake()->numberBetween(4, 10);
            for ($i = 0; $i < $count; $i++) {
                $category = $categories->random();

                Product::factory()
                    ->create([
                        'shop_id' => $shop->id,
                        'category_id' => $category->id,
                    ]);
            }
        }

        $this->command->info('Products seeded successfully with realistic names!');
    }
}
