<?php

use App\Livewire\Pages\CartPage;
use App\Livewire\Pages\CheckoutPage;
use App\Livewire\Pages\HomePage;
use App\Livewire\Pages\LoginPage;
use App\Livewire\Pages\OrderPaymentPage;
use App\Livewire\Pages\OrdersPage;
use App\Livewire\Pages\ProductDetailPage;
use App\Livewire\Pages\ProductsPage;
use App\Livewire\Pages\ShopDetailPage;
use App\Livewire\Pages\ShopsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::get('/', HomePage::class)->name('home');
Route::get('/login', LoginPage::class)->name('login');
Route::get('/products', ProductsPage::class)->name('products.index');

// Use :slug instead of implicit binding to avoid Livewire trying to resolve by ID
Route::get('/products/{slug}', ProductDetailPage::class)->name('products.show');
Route::get('/shops', ShopsPage::class)->name('shops.index');
Route::get('/shops/{slug}', ShopDetailPage::class)->name('shops.show');
Route::get('/cart', CartPage::class)->name('cart.index');
Route::get('/checkout', CheckoutPage::class)->name('checkout.index');
Route::get('/orders', OrdersPage::class)->name('orders.index');
Route::get('/orders/{order}/pay', OrderPaymentPage::class)->name('orders.pay');

Route::redirect('/dashboard', '/admin')->name('dashboard');

Route::get('/blog', function () {
    return view('coming-soon', ['title' => 'Blog & Artikel']);
})->name('blog.index');

Route::get('/test-signed', function (Request $req) {
    return $req->hasValidSignature() ? 'valid' : 'invalid';
})->name('test.signed');

Route::get('/make-test-url', function () {
    return URL::temporarySignedRoute('test.signed', now()->addMinutes(5));
});
