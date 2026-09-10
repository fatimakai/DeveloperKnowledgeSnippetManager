<?php

namespace Tests\Feature;

use App\Models\OAuthAccount;
use App\Models\User;
use App\Services\TwoFactorLoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_and_confirm_two_factor_setup(): void
    {
        $user = User::factory()->create();

        $setup = $this->actingAs($user)->post('/two-factor', ['password' => 'password']);

        $setup->assertRedirect(route('profile.edit'))
            ->assertSessionHas('two_factor.pending_recovery_codes', fn ($codes) => count($codes) === 8);
        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNotSame($user->two_factor_secret, DB::table('users')->whereKey($user->id)->value('two_factor_secret'));

        $this->get('/profile')
            ->assertOk()
            ->assertSee('Finish authenticator setup')
            ->assertSee('data:image/svg+xml;base64,', false)
            ->assertSee($user->two_factor_secret);

        $code = app(Google2FA::class)->getCurrentOtp($user->two_factor_secret);
        $confirm = $this->post('/two-factor/confirm', ['code' => $code]);

        $confirm->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'two-factor-enabled')
            ->assertSessionHas('two_factor_recovery_codes', fn ($codes) => count($codes) === 8);
        $this->assertTrue($user->fresh()->twoFactorEnabled());
        $this->assertNotNull($user->fresh()->two_factor_last_used_counter);
    }

    public function test_setup_requires_the_current_password_and_invalid_confirmation_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/two-factor', ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('twoFactorSetup', ['password']);

        $this->assertNull($user->fresh()->two_factor_secret);

        $this->post('/two-factor', ['password' => 'password']);
        $this->post('/two-factor/confirm', ['code' => '000000'])
            ->assertSessionHasErrorsIn('twoFactorConfirmation', ['code']);

        $this->assertFalse($user->fresh()->twoFactorEnabled());
    }

    public function test_password_login_requires_two_factor_challenge(): void
    {
        $user = User::factory()->create(['email' => 'secured@example.com']);
        $secret = $this->enableTwoFactor($user);

        $this->post('/login', [
            'email' => 'secured@example.com',
            'password' => 'password',
            'remember' => true,
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
        $this->assertSame($user->id, session(TwoFactorLoginService::USER_ID_KEY));

        $this->get('/two-factor-challenge')
            ->assertOk()
            ->assertSee('Authentication or recovery code');

        $this->post('/two-factor-challenge', [
            'code' => app(Google2FA::class)->getCurrentOtp($secret),
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertFalse(auth()->viaRemember());
        $this->assertNull(session(TwoFactorLoginService::USER_ID_KEY));
    }

    public function test_totp_codes_cannot_be_replayed(): void
    {
        $user = User::factory()->create(['email' => 'replay@example.com']);
        $secret = $this->enableTwoFactor($user);
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/two-factor-challenge', ['code' => $code])->assertRedirect('/dashboard');
        $this->post('/logout');

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/two-factor-challenge', ['code' => $code])
            ->assertSessionHasErrors(['code']);

        $this->assertGuest();
    }

    public function test_recovery_code_is_single_use(): void
    {
        $user = User::factory()->create(['email' => 'recovery@example.com']);
        $this->enableTwoFactor($user, ['abcde-12345']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/two-factor-challenge', ['code' => 'ABCDE-12345'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertSame([], $user->fresh()->two_factor_recovery_codes);

        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/two-factor-challenge', ['code' => 'abcde-12345'])
            ->assertSessionHasErrors(['code']);
    }

    public function test_challenge_expires_and_invalid_attempts_are_limited(): void
    {
        config(['two-factor.challenge_ttl' => 60, 'two-factor.max_attempts' => 2]);
        $user = User::factory()->create();
        $this->enableTwoFactor($user);

        $this->withSession([
            TwoFactorLoginService::USER_ID_KEY => $user->id,
            TwoFactorLoginService::STARTED_AT_KEY => now()->subSeconds(61)->timestamp,
        ])->get('/two-factor-challenge')
            ->assertRedirect(route('login'));

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/two-factor-challenge', ['code' => '000000'])->assertSessionHasErrors(['code']);
        $this->post('/two-factor-challenge', ['code' => '000000'])->assertSessionHasErrors(['code']);
        $this->post('/two-factor-challenge', ['code' => '000000'])
            ->assertSessionHasErrors(['code'])
            ->assertSessionHasErrorsIn('default', ['code']);

        $this->assertGuest();
    }

    public function test_recovery_codes_can_be_regenerated_and_two_factor_can_be_disabled(): void
    {
        $user = User::factory()->create();
        $this->enableTwoFactor($user, ['old12-code3']);

        $this->actingAs($user)
            ->post('/two-factor/recovery-codes', ['password' => 'password'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('two_factor_recovery_codes', fn ($codes) => count($codes) === 8);

        $this->assertCount(8, $user->fresh()->two_factor_recovery_codes);

        $this->delete('/two-factor', ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('twoFactorDisable', ['password']);
        $this->assertTrue($user->fresh()->twoFactorEnabled());

        $this->delete('/two-factor', ['password' => 'password'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'two-factor-disabled');

        $user->refresh();
        $this->assertFalse($user->twoFactorEnabled());
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
    }

    public function test_oauth_login_also_requires_two_factor_challenge(): void
    {
        $user = User::factory()->create();
        $this->enableTwoFactor($user);
        OAuthAccount::factory()->for($user)->create([
            'provider' => 'github',
            'provider_user_id' => 'secured-github-id',
        ]);
        config([
            'services.github.client_id' => 'client',
            'services.github.client_secret' => 'secret',
            'services.github.redirect' => 'http://localhost/auth/github/callback',
        ]);
        Socialite::fake('github', OAuthUser::fake([
            'id' => 'secured-github-id',
            'email' => $user->email,
        ]));

        $this->get('/auth/github/callback')->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
        $this->assertSame($user->id, session(TwoFactorLoginService::USER_ID_KEY));
    }

    public function test_api_login_requires_and_verifies_second_factor(): void
    {
        $user = User::factory()->create(['email' => 'api-2fa@example.com']);
        $secret = $this->enableTwoFactor($user);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['two_factor_code']);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'two_factor_code' => app(Google2FA::class)->getCurrentOtp($secret),
        ])->assertOk();

        $this->assertNotNull($response->json('token'));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_challenge_can_be_cancelled(): void
    {
        $user = User::factory()->create();
        $this->enableTwoFactor($user);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->delete('/two-factor-challenge')
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull(session(TwoFactorLoginService::USER_ID_KEY));
    }

    private function enableTwoFactor(User $user, array $recoveryCodes = ['first-recovery']): string
    {
        $secret = app(Google2FA::class)->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => array_map(fn ($code) => Hash::make(strtolower($code)), $recoveryCodes),
            'two_factor_confirmed_at' => now(),
            'two_factor_last_used_counter' => null,
        ])->save();

        return $secret;
    }
}
