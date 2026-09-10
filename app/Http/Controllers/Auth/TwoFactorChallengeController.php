<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthenticationService;
use App\Services\TwoFactorLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request, TwoFactorLoginService $login): View|RedirectResponse
    {
        if (! $login->pendingUser($request)) {
            return redirect()->route('login')->with('status', 'Your two-factor challenge expired. Please log in again.');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(
        Request $request,
        TwoFactorLoginService $login,
        TwoFactorAuthenticationService $twoFactor,
    ): RedirectResponse {
        $user = $login->pendingUser($request);

        if (! $user) {
            return redirect()->route('login')->with('status', 'Your two-factor challenge expired. Please log in again.');
        }

        $request->validate(['code' => ['required', 'string', 'max:32']]);
        $key = 'two-factor-challenge:'.$user->id.'|'.$request->ip();
        $maxAttempts = max(1, (int) config('two-factor.max_attempts'));

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'code' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        if (! $twoFactor->verify($user, $request->string('code')->toString())) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'code' => 'The authentication code is invalid or has already been used.',
            ]);
        }

        RateLimiter::clear($key);

        return $login->complete($request, $user);
    }

    public function destroy(Request $request, TwoFactorLoginService $login): RedirectResponse
    {
        $login->forget($request);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
