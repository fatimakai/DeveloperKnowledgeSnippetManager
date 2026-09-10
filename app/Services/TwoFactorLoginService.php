<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorLoginService
{
    public const USER_ID_KEY = 'login.two_factor_user_id';

    public const STARTED_AT_KEY = 'login.two_factor_started_at';

    public function login(Request $request, User $user, bool $remember = false): RedirectResponse
    {
        $request->session()->regenerate();

        if ($user->twoFactorEnabled()) {
            $request->session()->put([
                self::USER_ID_KEY => $user->id,
                self::STARTED_AT_KEY => now()->timestamp,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $remember);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get(self::USER_ID_KEY);
        $startedAt = $request->session()->get(self::STARTED_AT_KEY);
        $ttl = max(60, (int) config('two-factor.challenge_ttl'));

        if (! $userId || ! is_numeric($startedAt) || now()->timestamp - (int) $startedAt > $ttl) {
            $this->forget($request);

            return null;
        }

        $user = User::find($userId);

        if (! $user?->twoFactorEnabled()) {
            $this->forget($request);

            return null;
        }

        return $user;
    }

    public function complete(Request $request, User $user): RedirectResponse
    {
        $this->forget($request);
        Auth::login($user, false);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function forget(Request $request): void
    {
        $request->session()->forget([self::USER_ID_KEY, self::STARTED_AT_KEY]);
    }
}
