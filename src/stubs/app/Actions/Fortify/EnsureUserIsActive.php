<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    public function handle($request, $next)
    {
        $response = $next($request);

        $user = Auth::user();

        if ($user && !$user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => ['Your account is disabled.'],
            ]);
        }

        return $response;
    }
}
