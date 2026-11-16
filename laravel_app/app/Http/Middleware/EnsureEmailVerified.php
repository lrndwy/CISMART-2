<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    /**
     * Bila verifikasi email diwajibkan (config auth.verification.required),
     * area akun hanya bisa diakses setelah email diverifikasi. Pengecekan
     * dilakukan per-request supaya bisa dikonfigurasi lewat environment.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (config('auth.verification.required') && $user && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}