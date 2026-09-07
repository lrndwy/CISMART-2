<?php

use App\Livewire\Pages\HomePage;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('redirects guests to admin login when registering as seller', function () {
    Livewire::test(HomePage::class)
        ->call('handleSellerRegistration')
        ->assertRedirect('/admin/login');
});

it('redirects authenticated users to admin when registering as seller', function () {
    Role::findOrCreate('buyer');

    $user = User::factory()->create();
    $user->assignRole('buyer');

    $this->actingAs($user);

    Livewire::test(HomePage::class)
        ->call('handleSellerRegistration')
        ->assertRedirect('/admin');
});
