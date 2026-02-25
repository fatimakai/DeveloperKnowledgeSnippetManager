<?php

namespace Tests\Feature;

use App\Jobs\SendWelcomeEmailJob;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WelcomeEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_email_is_queued_on_registration()
    {
        Queue::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        Queue::assertPushed(SendWelcomeEmailJob::class);
    }

    public function test_welcome_email_is_sent_to_correct_user()
    {
        Queue::fake();

        $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        Queue::assertPushed(SendWelcomeEmailJob::class, function ($job) {
            return $job->user->email === 'john@example.com';
        });
    }

    public function test_welcome_mail_renders_correctly()
    {
        $user = User::factory()->create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
        ]);

        Mail::fake();

        Mail::send(new WelcomeMail($user));

        Mail::assertSent(WelcomeMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) &&
                   $mail->envelope()->subject === 'Welcome to Developer Knowledge Snippet Manager';
        });
    }

    public function test_welcome_mail_contains_user_name()
    {
        $user = User::factory()->create([
            'name' => 'Bob Johnson',
        ]);

        $mailable = new WelcomeMail($user);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Bob Johnson', $rendered);
    }

    public function test_send_welcome_email_job_sends_mail()
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        dispatch(new SendWelcomeEmailJob($user));

        Mail::assertSent(WelcomeMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_job_uses_redis_queue()
    {
        $user = User::factory()->create();
        $job = new SendWelcomeEmailJob($user);

        // Verify job is set to use redis connection
        $this->assertEquals('redis', $job->connection ?? config('queue.default'));
    }
}
