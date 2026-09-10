<?php

namespace Tests\Feature;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\OAuthAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use RuntimeException;
use Tests\TestCase;

class OAuthAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_registration_pages_offer_both_oauth_providers(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee('Continue with GitHub');

        $this->get('/register')
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee('Continue with GitHub');
    }

    public function test_configured_provider_redirects_to_oauth_authorization(): void
    {
        $this->configureProvider('google');
        Socialite::fake('google');

        $this->get('/auth/google/redirect')
            ->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_unconfigured_provider_returns_to_login_with_a_safe_error(): void
    {
        config([
            'services.github.client_id' => null,
            'services.github.client_secret' => null,
            'services.github.redirect' => null,
        ]);

        $this->get('/auth/github/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHas('oauth_error', 'GitHub login is not configured yet.');
    }

    public function test_unsupported_provider_is_not_routable(): void
    {
        $this->get('/auth/facebook/redirect')->assertNotFound();
        $this->get('/auth/facebook/callback')->assertNotFound();
    }

    public function test_google_callback_creates_verified_user_and_provider_account(): void
    {
        Queue::fake([SendWelcomeEmailJob::class]);
        $this->configureProvider('google');
        Socialite::fake('google', OAuthUser::fake([
            'id' => 'google-123',
            'name' => 'Ada Lovelace',
            'email' => 'ADA@EXAMPLE.COM',
            'email_verified' => true,
            'avatar' => 'https://example.com/ada.png',
        ]));

        $response = $this->get('/auth/google/callback');

        $user = User::where('email', 'ada@example.com')->firstOrFail();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertDatabaseHas('oauth_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => 'ada@example.com',
        ]);
        Queue::assertPushed(SendWelcomeEmailJob::class, fn ($job) => $job->user->is($user));
    }

    public function test_verified_provider_email_safely_links_an_existing_local_account(): void
    {
        Queue::fake([SendWelcomeEmailJob::class]);
        $user = User::factory()->unverified()->create([
            'name' => 'Existing Name',
            'email' => 'person@example.com',
        ]);
        $this->configureProvider('github');
        Socialite::fake('github', OAuthUser::fake([
            'id' => 'github-456',
            'name' => 'Replacement Name',
            'email' => 'person@example.com',
        ]));

        $this->get('/auth/github/callback')->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('Existing Name', $user->fresh()->name);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertDatabaseHas('oauth_accounts', [
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_user_id' => 'github-456',
        ]);
        Queue::assertNotPushed(SendWelcomeEmailJob::class);
    }

    public function test_returning_provider_identity_logs_into_its_linked_user(): void
    {
        $user = User::factory()->create();
        OAuthAccount::factory()->for($user)->create([
            'provider' => 'github',
            'provider_user_id' => 'known-github-id',
            'provider_email' => 'old@example.com',
        ]);
        $this->configureProvider('github');
        Socialite::fake('github', OAuthUser::fake([
            'id' => 'known-github-id',
            'email' => null,
            'avatar' => 'https://example.com/new-avatar.png',
        ]));

        $this->get('/auth/github/callback')->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('oauth_accounts', 1);
        $this->assertDatabaseHas('oauth_accounts', [
            'provider_user_id' => 'known-github-id',
            'provider_email' => 'old@example.com',
            'avatar_url' => 'https://example.com/new-avatar.png',
        ]);
    }

    public function test_new_login_without_a_verified_email_is_rejected(): void
    {
        $this->configureProvider('github');
        Socialite::fake('github', OAuthUser::fake([
            'id' => 'no-email-id',
            'email' => null,
        ]));

        $this->get('/auth/github/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('oauth_error', 'Your provider did not share a verified email address. Please allow email access or use password login.');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('oauth_accounts', 0);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->configureProvider('google');
        Socialite::fake('google', OAuthUser::fake([
            'id' => 'unverified-google-id',
            'email' => 'unverified@example.com',
            'email_verified' => false,
        ]));

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('oauth_error', 'Google did not confirm this email address as verified.');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_provider_cannot_replace_a_different_link_for_the_same_user(): void
    {
        $user = User::factory()->create(['email' => 'linked@example.com']);
        OAuthAccount::factory()->for($user)->create([
            'provider' => 'google',
            'provider_user_id' => 'original-google-id',
        ]);
        $this->configureProvider('google');
        Socialite::fake('google', OAuthUser::fake([
            'id' => 'different-google-id',
            'email' => 'linked@example.com',
            'email_verified' => true,
        ]));

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('oauth_error', 'This email already has a different Google account linked.');

        $this->assertGuest();
        $this->assertDatabaseCount('oauth_accounts', 1);
    }

    public function test_provider_failures_do_not_leak_internal_errors(): void
    {
        $this->configureProvider('github');
        Socialite::fake('github', fn () => throw new RuntimeException('secret provider response'));

        $this->get('/auth/github/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('oauth_error', 'We could not complete that social login. Please try again.')
            ->assertSessionMissing('secret provider response');

        $this->assertGuest();
    }

    private function configureProvider(string $provider): void
    {
        config([
            "services.{$provider}.client_id" => 'test-client-id',
            "services.{$provider}.client_secret" => 'test-client-secret',
            "services.{$provider}.redirect" => "http://localhost/auth/{$provider}/callback",
        ]);
    }
}
