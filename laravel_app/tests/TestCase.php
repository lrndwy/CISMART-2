<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Docker compose menyuntik variabel seperti APP_ENV=local / DB_CONNECTION=pgsql
     * ke lingkungan container. Laravel membaca $_SERVER lebih dulu daripada $_ENV,
     * sehingga <env force="true"> di phpunit.xml tidak cukup. Tanpa pemaksaan di
     * sini, RefreshDatabase akan menjalankan migrate:fresh di database dev.
     *
     * @var array<string, string>
     */
    protected array $forcedTestEnvironment = [
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => ':memory:',
        'DB_HOST' => '',
        'DB_PORT' => '',
        'DB_USERNAME' => '',
        'DB_PASSWORD' => '',
        'REQUIRE_EMAIL_VERIFICATION' => 'false',
    ];

    protected function setUp(): void
    {
        foreach ($this->forcedTestEnvironment as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        parent::setUp();

        $this->guardAgainstRealDatabase();
    }

    /**
     * Jaring pengaman terakhir: jangan pernah biarkan suite ini menyentuh
     * database non-test (RefreshDatabase akan menghapus seluruh isinya).
     */
    protected function guardAgainstRealDatabase(): void
    {
        if (env('TESTS_ALLOW_REAL_DB')) {
            return;
        }

        if (config('database.default') !== 'sqlite') {
            throw new RuntimeException(
                'Test suite harus berjalan di sqlite in-memory, tetapi koneksi aktif adalah "'
                .config('database.default').'". Set TESTS_ALLOW_REAL_DB=1 bila memang ingin menjalankan test di database nyata.'
            );
        }
    }
}