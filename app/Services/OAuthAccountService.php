<?php

namespace App\Services;

use App\Exceptions\OAuthLoginException;
use App\Models\OAuthAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;

class OAuthAccountService
{
    public const PROVIDERS = ['google', 'github'];

    public function resolve(string $provider, ProviderUser $profile): User
    {
        if (! in_array($provider, self::PROVIDERS, true)) {
            throw new OAuthLoginException('That login provider is not supported.');
        }

        $providerUserId = trim((string) $profile->getId());

        if ($providerUserId === '') {
            throw new OAuthLoginException('The login provider did not return an account identifier.');
        }

        return DB::transaction(function () use ($provider, $profile, $providerUserId): User {
            $account = OAuthAccount::query()
                ->where('provider', $provider)
                ->where('provider_user_id', $providerUserId)
                ->lockForUpdate()
                ->first();

            if ($account) {
                $this->refreshAccount($account, $profile);

                return $account->user;
            }

            $email = $this->verifiedEmail($provider, $profile);
            $user = User::query()->where('email', $email)->lockForUpdate()->first();

            if ($user?->oauthAccounts()->where('provider', $provider)->exists()) {
                $providerName = $provider === 'github' ? 'GitHub' : 'Google';

                throw new OAuthLoginException("This email already has a different {$providerName} account linked.");
            }

            if ($user) {
                if (! $user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }
            } else {
                $user = User::create([
                    'name' => $this->name($profile, $email),
                    'email' => $email,
                    'password' => Str::random(64),
                ]);
                $user->markEmailAsVerified();
            }

            $user->oauthAccounts()->create([
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'provider_email' => $email,
                'avatar_url' => $this->avatar($profile),
            ]);

            return $user;
        }, 3);
    }

    private function verifiedEmail(string $provider, ProviderUser $profile): string
    {
        $email = Str::lower(trim((string) $profile->getEmail()));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new OAuthLoginException('Your provider did not share a verified email address. Please allow email access or use password login.');
        }

        if ($provider === 'google') {
            $raw = method_exists($profile, 'getRaw') ? $profile->getRaw() : [];
            $verified = $raw['email_verified'] ?? $raw['verified_email'] ?? false;

            if (! filter_var($verified, FILTER_VALIDATE_BOOL)) {
                throw new OAuthLoginException('Google did not confirm this email address as verified.');
            }
        }

        return $email;
    }

    private function name(ProviderUser $profile, string $email): string
    {
        $name = trim((string) ($profile->getName() ?: $profile->getNickname()));

        return Str::limit($name !== '' ? $name : Str::before($email, '@'), 255, '');
    }

    private function refreshAccount(OAuthAccount $account, ProviderUser $profile): void
    {
        $email = Str::lower(trim((string) $profile->getEmail()));

        $account->update([
            'provider_email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : $account->provider_email,
            'avatar_url' => $this->avatar($profile) ?: $account->avatar_url,
        ]);
    }

    private function avatar(ProviderUser $profile): ?string
    {
        $avatar = trim((string) $profile->getAvatar());

        return $avatar !== '' ? Str::limit($avatar, 2048, '') : null;
    }
}
