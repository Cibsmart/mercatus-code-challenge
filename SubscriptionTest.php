<?php

namespace Tests\Feature;

use App\Mail\SubscriberJoined;
use App\Subscriber;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_subscriber_can_subscribe_to_waitlist()
    {
        $this->withoutExceptionHandling();

        $response = $this->post(route('waitlist'), ['email' => 'hello@example.com']);

        $this->assertCount(1, Subscriber::all());

        $response->assertRedirect(route('subscribed'));
    }

    /** @test */
    public function an_email_is_required_for_a_new_subscription()
    {
        $response = $this->post(route('waitlist'), ['email' => '']);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function an_email_for_new_subscription_must_be_unique()
    {
        $this->post(route('waitlist'), ['email' => 'hello@example.com']);

        $this->assertCount(1, Subscriber::all());

        $response = $this->post(route('waitlist'), ['email' => 'hello@example.com']);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function an_email_for_new_subscription_must_be_valid()
    {
        $response = $this->post(route('waitlist'), ['email' => 'helloexample.com']);
        $response->assertSessionHasErrors('email');

        $response = $this->post(route('waitlist'), ['email' => '@example.com']);
        $response->assertSessionHasErrors('email');

        $response = $this->post(route('waitlist'), ['email' => 'hello@.com']);
        $response->assertSessionHasErrors('email');
    }


    /** @test */
    public function an_email_is_sent_upon_new_subscription_to_the_waitlist()
    {
        Mail::fake();

        Mail::assertNotQueued(SubscriberJoined::class);

        $this->post(route('waitlist'), ['email' => 'hello@example.com']);

        Mail::assertQueued(SubscriberJoined::class);
    }
}
