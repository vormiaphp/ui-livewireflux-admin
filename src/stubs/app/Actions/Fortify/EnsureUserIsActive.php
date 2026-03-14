<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EnsureUserIsActive
{
    /**
     * Ensure the authenticated user is active (is_active flag).
     * Run this in the Fortify auth pipeline after AttemptToAuthenticate.
     * See docs/FORTIFY-IS-ACTIVE.md for registration.
     */
    public function __invoke(Request $request, callable $next)
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => [__('Your account is disabled.')],
            ]);
        }

        return $next($request);
    }
}
