<?php

namespace Tests\Fieldtypes;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as Router;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\TwoFactor;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userWithTwoFactorEnabled();
        $this->actingAs($this->user);

        $request = $this->createRequestWithParameters('statamic.cp.users.edit', [
            'user' => $this->user->id,
        ]);

        app()->instance('request', $request);
    }

    #[Test]
    public function correctly_returns_is_locked()
    {
        $this->assertFalse($this->user->two_factor_locked);
        $this->assertFalse($this->fieldtype()->preload()['is_locked']);

        $this->user->set('two_factor_locked', true)->save();

        $this->assertTrue($this->user->two_factor_locked);
        $this->assertTrue($this->fieldtype()->preload()['is_locked']);
    }

    #[Test]
    public function correctly_returns_is_me()
    {
        $this->assertTrue($this->fieldtype()->preload()['is_me']);

        $this->actingAs($this->userWithTwoFactorEnabled());

        $this->assertFalse($this->fieldtype()->preload()['is_me']);
    }

    #[Test]
    public function correctly_returns_is_setup()
    {
        $this->assertTrue($this->fieldtype()->preload()['is_setup']);

        // Create a new user, without two factor enabled.
        $user = $this->user();

        $request = $this->createRequestWithParameters('statamic.cp.users.edit', [
            'user' => $user->id,
        ]);

        app()->instance('request', $request);

        $this->assertFalse($this->fieldtype()->preload()['is_setup']);
    }

    #[Test]
    public function correctly_returns_routes_for_me()
    {
        // When unlocked...
        $preload = $this->fieldtype()->preload();

        $this->assertArrayHasKey('locked', $preload['routes']);
        $this->assertNull($this->fieldtype()->preload()['routes']['locked']);

        $this->assertArrayHasKey('recovery_codes', $preload['routes']);
        $this->assertEquals(cp_route('users.two-factor.recovery-codes.show', $this->user->id), $preload['routes']['recovery_codes']['show']);
        $this->assertEquals(cp_route('users.two-factor.recovery-codes.generate', $this->user->id), $preload['routes']['recovery_codes']['generate']);

        $this->assertArrayHasKey('reset', $preload['routes']);
        $this->assertEquals(cp_route('users.two-factor.reset', $this->user->id), $preload['routes']['reset']);

        // When locked...
        $this->user->set('two_factor_locked', true)->save();
        $preload = $this->fieldtype()->preload();

        $this->assertArrayHasKey('locked', $preload['routes']);
        $this->assertEquals(cp_route('users.two-factor.unlock', $this->user->id), $this->fieldtype()->preload()['routes']['locked']);

        $this->assertArrayHasKey('recovery_codes', $preload['routes']);
        $this->assertEquals(cp_route('users.two-factor.recovery-codes.show', $this->user->id), $preload['routes']['recovery_codes']['show']);
        $this->assertEquals(cp_route('users.two-factor.recovery-codes.generate', $this->user->id), $preload['routes']['recovery_codes']['generate']);

        $this->assertArrayHasKey('reset', $preload['routes']);
        $this->assertEquals(cp_route('users.two-factor.reset', $this->user->id), $preload['routes']['reset']);
    }

    #[Test]
    public function correctly_returns_routes_for_another_user()
    {
        $this->user->delete();

        $user = $this->userWithTwoFactorEnabled();

        $preload = $this->createRequestWithParameters('statamic.cp.users.edit', [
            'user' => $user->id,
        ]);

        app()->instance('request', $preload);

        // When unlocked...
        $preload = $this->fieldtype()->preload();

        $this->assertArrayHasKey('locked', $preload['routes']);
        $this->assertNull($this->fieldtype()->preload()['routes']['locked']);

        $this->assertArrayHasKey('recovery_codes', $preload['routes']);
        $this->assertNull($preload['routes']['recovery_codes']['show']);
        $this->assertNull($preload['routes']['recovery_codes']['generate']);

        $this->assertArrayHasKey('reset', $preload['routes']);
        $this->assertEquals(cp_route('users.two-factor.reset', $user->id), $preload['routes']['reset']);

        // When locked...
        $user->set('two_factor_locked', true)->save();
        $preload = $this->fieldtype()->preload();

        $this->assertArrayHasKey('locked', $preload['routes']);
        $this->assertEquals(cp_route('users.two-factor.unlock', $user->id), $this->fieldtype()->preload()['routes']['locked']);

        $this->assertArrayHasKey('recovery_codes', $preload['routes']);
        $this->assertNull($preload['routes']['recovery_codes']['show']);
        $this->assertNull($preload['routes']['recovery_codes']['generate']);

        $this->assertArrayHasKey('reset', $preload['routes']);
        $this->assertEquals(cp_route('users.two-factor.reset', $user->id), $preload['routes']['reset']);
    }

    private function fieldtype($config = [])
    {
        return (new TwoFactor())->setField(new Field('test', array_merge(['type' => 'two_factor'], $config)));
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

    // Based on:
    // https://gist.github.com/juampi92/fff250719122a596c716c64e5b0afef6
    private function createRequestWithParameters(string $routeName, array $parameters = [], string $class = Request::class)
    {
        // Find the route properties.
        $route = Router::getRoutes()->getByName($routeName);

        throw_if(is_null($route),
            new Exception("[Pest.php createRequestWithParameters] Couldn't find route by the name of {$routeName}."));

        // Recreate the full url
        $fullUrl = route($routeName, $parameters);

        $method = $route->methods()[0];
        $uri = $route->uri;

        $request = $class::create($fullUrl);
        $request->setRouteResolver(function () use ($request, $method, $uri) {
            // Associate Route to request so we can access route parameters.
            return (new Route($method, $uri, []))->bind($request);
        });

        return $request;
    }
}
