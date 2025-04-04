<?php

namespace Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\Role;
use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\EnforceTwoFactor;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EnforceTwoFactorTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.users.two_factor.enabled', true);
    }

    #[Test]
    public function moves_to_the_next_middleware_when_two_factor_is_disabled()
    {
        // Disable
        config()->set('statamic.users.two_factor.enabled', false);

        $this->actingAs($user = $this->userWithTwoFactorEnabled());

        $request = Request::create(cp_route('index'));
        $request->setUserResolver(fn () => $user);

        $response = (new EnforceTwoFactor)->handle($request, fn () => null);

        $this->assertNull($response);

        // Enable
        config()->set('statamic.users.two_factor.enabled', true);

        $this->actingAs($user = $this->userWithTwoFactorEnabled());

        $request = Request::create(cp_route('index'));
        $request->setUserResolver(fn () => $user);

        $response = (new EnforceTwoFactor)->handle($request, fn () => null);

        $this->assertTrue($response->isRedirection());
    }

    #[Test]
    public function redirects_to_the_setup_route_when_two_factor_setup_is_not_completed()
    {
        $this->actingAs($user = $this->user());

        $request = Request::create(cp_route('index'));
        $request->setUserResolver(fn () => $user);

        $response = (new EnforceTwoFactor)->handle($request, fn () => null);

        $this->assertTrue($response->isRedirect(cp_route('two-factor.setup')));
    }

    #[Test]
    public function redirects_to_the_setup_route_when_two_factor_setup_is_not_completed_when_the_user_is_super()
    {
        config()->set('statamic-two-factor.enforced_roles', []);

        $this->actingAs($user = $this->user());

        $request = Request::create(cp_route('index'));
        $request->setUserResolver(fn () => $user);

        $response = (new EnforceTwoFactor)->handle($request, fn () => null);

        $this->assertTrue($response->isRedirect(cp_route('two-factor.setup')));
    }

    #[Test]
    public function redirects_to_the_setup_route_when_two_factor_setup_is_not_completed_when_the_user_has_an_enforced_role()
    {
        $enforceableRole = Role::make('enforceable_role')->save();

        $user = $this->user()->set('super', false)->assignRole($enforceableRole);
        $user->save();

        $this->actingAs($user);

        // Not enforced for now...
        config()->set('statamic.users.two_factor.enforced_roles', []);

        $request = Request::create(cp_route('index'));
        $request->setUserResolver(fn () => $user);

        $response = (new EnforceTwoFactor)->handle($request, fn () => null);

        $this->assertNull($response);

        // Enforce the role...
        config()->set('statamic.users.two_factor.enforced_roles', [
            'enforceable_role',
        ]);

        $request = Request::create(cp_route('index'));
        $request->setUserResolver(fn () => $user);

        $response = (new EnforceTwoFactor)->handle($request, fn () => null);

        $this->assertTrue($response->isRedirect(cp_route('two-factor.setup')));
    }

    #[Test]
    public function redirects_to_the_challenge_when_validity_is_enabled_and_there_is_no_recent_challenge_or_it_has_expired()
    {
        config()->set('statamic.users.two_factor.validity', 1); // 1 minute

        $this
            ->actingAs($user = $this->userWithTwoFactorEnabled())
            ->get(cp_route('collections.index'))
            ->assertRedirect(cp_route('two-factor.challenge'));

        // Set the time and force a challenge (ie. fake it)
        $this->freezeTime();
        TwoFactorUser::setLastChallenged($user);

        $this
            ->get(cp_route('collections.index'))
            ->assertOk();

        // Jump forward 2 minutes
        $this->travel(2)->minutes();

        $this
            ->get(cp_route('collections.index'))
            ->assertRedirect(cp_route('two-factor.challenge'));

        // However, a POST or PATCH will not redirect, to prevent losing things.
        $this
            ->post(cp_route('users.two-factor.recovery-codes.generate', ['user' => $user->id]))
            ->assertOk();
    }

    #[Test]
    public function redirects_to_the_challenge_when_validity_is_disabled_and_there_is_no_recent_challenge()
    {
        config()->set('statamic-two-factor.validity', null);

        $this
            ->actingAs($user = $this->userWithTwoFactorEnabled())
            ->get(cp_route('collections.index'))
            ->assertRedirect(cp_route('two-factor.challenge'));

        // Set the time and force a challenge (ie. fake it)
        $this->freezeTime();
        TwoFactorUser::setLastChallenged($user);

        // Jump forward 5 minutes to ensure we're not redirected.
        $this->travel(5)->minutes();

        $this
            ->get(cp_route('collections.index'))
            ->assertOk();

        // And, jump ahead another 5 hours to ensure we're not redirected.
        $this->travel(5)->hours();

        $this
            ->get(cp_route('collections.index'))
            ->assertOk();
    }

    #[Test]
    public function redirects_to_challenge_when_super_user()
    {
        $this->actingAs($user = $this->userWithTwoFactorEnabled());

        $request = Request::create(cp_route('dashboard'));
        $request->setUserResolver(fn () => $user);

        // Standard - should redirect...
        config()->set('statamic.users.two_factor.enforced_roles', null);

        $response = (new EnforceTwoFactor)->handle($request, fn () => response('No enforcement'));

        $this->assertTrue($response->isRedirect(cp_route('two-factor.challenge')));

        // Specific roles - should redirect...
        config()->set('statamic-two-factor.enforced_roles', []);

        $response = (new EnforceTwoFactor)->handle($request, fn () => response('No enforcement'));

        $this->assertTrue($response->isRedirect(cp_route('two-factor.challenge')));
    }

    #[Test]
    public function redirects_to_challenge_when_two_factor_is_enabled_and_when_no_enforced_roles_are_provided()
    {
        $enforceableRole = Role::make('enforceable_role')->save();

        $user = $this->userWithTwoFactorEnabled()->set('super', false)->assignRole($enforceableRole);
        $user->save();

        $this->actingAs($user);

        $request = Request::create(cp_route('dashboard'));
        $request->setUserResolver(fn () => $user);

        // All roles - should redirect...
        config()->set('statamic.users.two_factor.enforced_roles', null);

        $response = (new EnforceTwoFactor)->handle($request, fn () => response('No enforcement'));

        $this->assertTrue($response->isRedirect(cp_route('two-factor.challenge')));

        // Specific roles - none provided meaning not enforced...
        config()->set('statamic.users.two_factor.enforced_roles', []);

        $response = (new EnforceTwoFactor)->handle($request, fn () => response('No enforcement'));

        $this->assertTrue($response->isRedirect(cp_route('two-factor.challenge')));

        // Specific roles - should redirect...
        config()->set('statamic.users.two_factor.enforced_roles', [
            'enforceable_role',
        ]);

        $response = (new EnforceTwoFactor)->handle($request, fn () => response('No enforcement'));

        $this->assertTrue($response->isRedirect(cp_route('two-factor.challenge')));
    }

    private function user()
    {
        return tap(User::make()->makeSuper())->save();
    }

    private function userWithTwoFactorEnabled()
    {
        $user = $this->user();

        $user->merge([
            'two_factor_locked' => false,
            'two_factor_confirmed_at' => now(),
            'two_factor_completed' => now(),
            'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }
}
