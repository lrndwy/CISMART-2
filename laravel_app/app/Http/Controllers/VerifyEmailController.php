<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Tandai email terverifikasi. Rute ini dilindungi middleware `signed`
     * dan `throttle`, jadi tautan hanya sah bila berasal dari email kita.
     */
    public function __invoke(Request $request, string $id, string $hash)
    {
        $user = $request->user();

        abort_unless($user !== null, 403);
        abort_unless((string) $user->getKey() === $id, 403);
        abort_unless(hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route('orders.index')->with('verified', true);
    }
}