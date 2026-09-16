<?php

use App\Livewire\Pages\HomePage;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('redirects guests to the UMKM registration page', function () {
    Livewire::test(HomePage::class)
        ->call('handleSellerRegistration')
        ->assertRedirect('/register/umkm');
});

it('redirects authenticated users to the UMKM registration page', function () {
    Role::findOrCreate('buyer');

    $user = User::factory()->create();
    $user->assignRole('buyer');

    $this->actingAs($user);

    Livewire::test(HomePage::class)
        ->call('handleSellerRegistration')
        ->assertRedirect('/register/umkm');
});
