<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OAuthLoginException;
use App\Http\Controllers\Controller;
use App\Jobs\SendWelcomeEmailJob;
use App\Services\OAuthAccountService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class OAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureSupported($provider);

        if (! $this->configured($provider)) {
            return redirect()->route('login')->with('oauth_error', $this->providerName($provider).' login is not configured yet.');
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider, OAuthAccountService $accounts): RedirectResponse
    {
        $this->ensureSupported($provider);

        if (! $this->configured($provider)) {
            return redirect()->route('login')->with('oauth_error', $this->providerName($provider).' login is not configured yet.');
        }

        try {
            $user = $accounts->resolve($provider, Socialite::driver($provider)->user());
        } catch (OAuthLoginException $exception) {
            return redirect()->route('login')->with('oauth_error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->with('oauth_error', 'We could not complete that social login. Please try again.');
        }

        if ($user->wasRecentlyCreated) {
            event(new Registered($user));
            SendWelcomeEmailJob::dispatch($user);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function ensureSupported(string $provider): void
    {
        abort_unless(in_array($provider, OAuthAccountService::PROVIDERS, true), 404);
    }

    private function configured(string $provider): bool
    {
        return filled(config("services.{$provider}.client_id"))
            && filled(config("services.{$provider}.client_secret"))
            && filled(config("services.{$provider}.redirect"));
    }

    private function providerName(string $provider): string
    {
        return $provider === 'github' ? 'GitHub' : 'Google';
    }
}
