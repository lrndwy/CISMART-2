<?php

it('menampilkan halaman Syarat & Ketentuan untuk tamu', function () {
    $response = $this->get('/syarat-ketentuan');

    $response->assertOk()
        ->assertSee('Syarat')
        ->assertSee('Versi '.config('legal.terms.version'))
        ->assertSee('Daftar isi');
});

it('memakai versi dari config, bukan teks yang ditulis di view', function () {
    config(['legal.terms.version' => '9.9']);

    $this->get('/syarat-ketentuan')->assertSee('Versi 9.9');
});

it('memberi anchor stabil untuk setiap pasal S&K', function () {
    $html = $this->get('/syarat-ketentuan')->assertOk()->getContent();

    $anchors = [
        'definisi', 'akun', 'kewajiban-penjual', 'kewajiban-pembeli',
        'transaksi', 'larangan', 'sanksi', 'hukum',
    ];

    foreach ($anchors as $anchor) {
        expect($html)->toContain('id="'.$anchor.'"')
            ->and($html)->toContain('href="#'.$anchor.'"');
    }
});

it('menampilkan halaman Perjanjian Penjual untuk tamu', function () {
    $this->get('/perjanjian-penjual')
        ->assertOk()
        ->assertSee('Perjanjian Penjual')
        ->assertSee('Versi '.config('legal.seller_agreement.version'))
        ->assertSee(route('legal.terms'));
});

it('menautkan kedua dokumen legal satu sama lain', function () {
    $this->get('/syarat-ketentuan')->assertSee(route('legal.seller-agreement'));
    $this->get('/perjanjian-penjual')->assertSee(route('legal.terms'));
});

it('menautkan Syarat & Ketentuan dari footer beranda', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.route('legal.terms').'"', false);
});