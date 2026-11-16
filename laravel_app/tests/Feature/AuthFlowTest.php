<?php

use App\Livewire\Pages\ForgotPasswordPage;
use App\Livewire\Pages\LoginPage;
use App\Livewire\Pages\RegisterPage;
use App\Livewire\Pages\ResetPasswordPage;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('buyer');
    Role::findOrCreate('seller');
});

it('membatasi percobaan login berulang', function () {
    User::factory()->create(['email' => 'brute@uji.test']);

    for ($i = 0; $i < 5; $i++) {
        Livewire::test(LoginPage::class)
            ->set('email', 'brute@uji.test')
            ->set('password', 'salah-terus')
            ->call('login');
    }

    $component = Livewire::test(LoginPage::class)
        ->set('email', 'brute@uji.test')
        ->set('password', 'salah-terus')
        ->call('login');

    expect($component->errors()->first('email'))->toContain('Terlalu banyak percobaan');
});

it('mengizinkan login dengan kredensial benar dan mengingat sesi bila diminta', function () {
    $user = User::factory()->create([
        'email' => 'benar@uji.test',
        'password' => Hash::make('rahasia123'),
    ]);

    Livewire::test(LoginPage::class)
        ->set('email', 'benar@uji.test')
        ->set('password', 'rahasia123')
        ->set('remember', true)
        ->call('login')
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user->fresh());
});

it('mengirim tautan reset kata sandi', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'lupa@uji.test']);

    Livewire::test(ForgotPasswordPage::class)
        ->set('email', 'lupa@uji.test')
        ->call('sendResetLink')
        ->assertSet('sent', true);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('mengubah kata sandi lewat token reset', function () {
    $user = User::factory()->create([
        'email' => 'reset@uji.test',
        'password' => Hash::make('lama12345'),
    ]);

    $token = Password::createToken($user);

    Livewire::test(ResetPasswordPage::class, ['token' => $token])
        ->set('email', 'reset@uji.test')
        ->set('password', 'baru123456')
        ->set('password_confirmation', 'baru123456')
        ->call('resetPassword');

    expect(Hash::check('baru123456', $user->fresh()->password))->toBeTrue();
});

it('menolak token reset yang salah', function () {
    $user = User::factory()->create([
        'email' => 'resetsalah@uji.test',
        'password' => Hash::make('lama12345'),
    ]);

    Livewire::test(ResetPasswordPage::class, ['token' => 'token-palsu'])
        ->set('email', 'resetsalah@uji.test')
        ->set('password', 'baru123456')
        ->set('password_confirmation', 'baru123456')
        ->call('resetPassword')
        ->assertHasErrors('email');

    expect(Hash::check('lama12345', $user->fresh()->password))->toBeTrue();
});

it('mengirim email verifikasi saat registrasi pembeli', function () {
    Notification::fake();

    Livewire::test(RegisterPage::class)
        ->set('name', 'Pembeli Verifikasi')
        ->set('email', 'verifikasi@uji.test')
        ->set('phone', '081234567890')
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->call('register');

    $user = User::where('email', 'verifikasi@uji.test')->firstOrFail();

    Notification::assertSentTo($user, VerifyEmail::class);
    expect($user->hasVerifiedEmail())->toBeFalse();
});

it('memblokir area akun bagi email yang belum diverifikasi', function () {
    config(['auth.verification.required' => true]);

    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)->get('/orders')->assertRedirect(route('verification.notice'));
});

it('mengizinkan area akun bila verifikasi email tidak diwajibkan', function () {
    config(['auth.verification.required' => false]);

    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)->get('/orders')->assertOk();
});

it('memverifikasi email lewat tautan bertanda tangan', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(5), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect(route('orders.index'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('menolak tautan verifikasi tanpa tanda tangan yang sah', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)
        ->get("/email/verify/{$user->id}/".sha1($user->email))
        ->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('tidak mendaftarkan rute alat bantu tanda tangan di luar lingkungan lokal', function () {
    $this->get('/test-signed')->assertNotFound();
    $this->get('/make-test-url')->assertNotFound();
});