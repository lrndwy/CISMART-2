<?php

use App\Http\Controllers\VerifyEmailController;
use App\Livewire\Pages\CartPage;
use App\Livewire\Pages\CheckoutPage;
use App\Livewire\Pages\ForgotPasswordPage;
use App\Livewire\Pages\HomePage;
use App\Livewire\Pages\LoginPage;
use App\Livewire\Pages\OrderPaymentPage;
use App\Livewire\Pages\OrdersPage;
use App\Livewire\Pages\ProductDetailPage;
use App\Livewire\Pages\ProductsPage;
use App\Livewire\Pages\ProfilePage;
use App\Livewire\Pages\RegisterPage;
use App\Livewire\Pages\RegisterUmkmPage;
use App\Livewire\Pages\ResetPasswordPage;
use App\Livewire\Pages\ShopDetailPage;
use App\Livewire\Pages\ShopsPage;
use App\Livewire\Pages\VerifyEmailPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');
Route::get('/login', LoginPage::class)->name('login');
Route::get('/register', RegisterPage::class)->name('register');
Route::get('/register/umkm', RegisterUmkmPage::class)->name('register.umkm');
Route::get('/products', ProductsPage::class)->name('products.index');

// Use :slug instead of implicit binding to avoid Livewire trying to resolve by ID
Route::get('/products/{slug}', ProductDetailPage::class)->name('products.show');
Route::get('/shops', ShopsPage::class)->name('shops.index');
Route::get('/shops/{slug}', ShopDetailPage::class)->name('shops.show');
Route::get('/cart', CartPage::class)->name('cart.index');

// Lupa / reset kata sandi
Route::get('/forgot-password', ForgotPasswordPage::class)->name('password.request');
Route::get('/reset-password/{token}', ResetPasswordPage::class)->name('password.reset');

// Verifikasi email
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', VerifyEmailPage::class)->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

/*
| Area akun. Bila REQUIRE_EMAIL_VERIFICATION=true (default), area ini
| hanya terbuka setelah email diverifikasi. Set false untuk demo lokal
| tanpa mail catcher.
*/
Route::middleware(['auth', 'verified.email'])->group(function () {
    Route::get('/checkout', CheckoutPage::class)->name('checkout.index');
    Route::get('/orders', OrdersPage::class)->name('orders.index');
    Route::get('/orders/{order}/pay', OrderPaymentPage::class)->name('orders.pay');
    Route::get('/profile', ProfilePage::class)->name('profile');
});

// Dashboard: admin/penjual ke panel, pembeli ke daftar pesanan.
Route::get('/dashboard', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->hasAnyRole(['admin', 'seller'])) {
        return redirect('/admin');
    }

    return redirect()->route('orders.index');
})->middleware('auth')->name('dashboard');

Route::get('/blog', function () {
    return view('coming-soon', ['title' => 'Blog & Artikel']);
})->name('blog.index');

/*
| Dokumen legal. Teksnya Blade statis; versi dan tanggal berlakunya dari
| config/legal.php supaya halaman, label persetujuan, dan baris audit memakai
| nilai yang sama.
*/
Route::view('/syarat-ketentuan', 'legal.terms', ['title' => 'Syarat & Ketentuan — CISMART'])
    ->name('legal.terms');

Route::view('/perjanjian-penjual', 'legal.seller-agreement', ['title' => 'Perjanjian Penjual — CISMART'])
    ->name('legal.seller-agreement');

// Rute alat bantu pengujian tanda tangan: hanya tersedia di lingkungan lokal.
if (app()->environment('local')) {
    Route::get('/test-signed', function (Illuminate\Http\Request $req) {
        return $req->hasValidSignature() ? 'valid' : 'invalid';
    })->name('test.signed');

    Route::get('/make-test-url', function () {
        return Illuminate\Support\Facades\URL::temporarySignedRoute('test.signed', now()->addMinutes(5));
    });
}