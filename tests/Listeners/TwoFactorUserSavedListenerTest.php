<?php

namespace Tests\Listeners;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\UserSaved;
use Statamic\Facades\User;
use Statamic\Listeners\TwoFactorUserSavedListener;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TwoFactorUserSavedListenerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function listens_for_the_user_saved_event_when_creating_a_user()
    {
        Event::fake();

        $user = tap(User::make()->makeSuper())->save();

        Event::assertListening(UserSaved::class, TwoFactorUserSavedListener::class);
    }

    #[Test]
    public function listens_for_the_user_saved_event_when_updating_a_user()
    {
        $user = tap(User::make()->makeSuper())->save();

        Event::fake();

        $user->set('name', 'Johnny')->save();

        Event::assertListening(UserSaved::class, TwoFactorUserSavedListener::class);
    }

    #[Test]
    public function listens_for_the_user_saved_event_when_deleting_a_user()
    {
        $user = tap(User::make()->makeSuper())->save();

        Event::fake();

        $user->delete();

        Event::assertListening(UserSaved::class, TwoFactorUserSavedListener::class);
    }

    #[Test]
    public function correctly_updates_the_two_factor_summary_for_the_user()
    {
        // Should update "locked" and "setup" based on the setup of the user.
        // We'll fudge it for the test to mock the fields being set.

        $user = tap(User::make()->makeSuper())->save();

        $this->assertIsArray($user->two_factor);
        $this->assertEquals([
            'cancellable' => true,
            'locked' => false,
            'setup' => false,
        ], $user->two_factor);

        // Mark as set up.
        $user->set('two_factor_confirmed_at', now())->save();

        $this->assertEquals([
            'cancellable' => false,
            'locked' => false,
            'setup' => true,
        ], $user->two_factor);

        // Mark as locked.
        $user->set('two_factor_locked', now())->save();

        $this->assertEquals([
            'cancellable' => false,
            'locked' => true,
            'setup' => true,
        ], $user->two_factor);

        // Unlock.
        $user->set('two_factor_locked', false)->save();

        $this->assertEquals([
            'cancellable' => false,
            'locked' => false,
            'setup' => true,
        ], $user->two_factor);

        // Reset
        $user->set('two_factor_confirmed_at', null)->save();

        $this->assertEquals([
            'cancellable' => true,
            'locked' => false,
            'setup' => false,
        ], $user->two_factor);
    }
}
