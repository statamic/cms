<?php

namespace Tests\Fieldtypes;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as Router;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\TwoFactor;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_preload_for_current_user()
    {
        $this->actingAs($user = $this->user());

        $request = $this->createRequestWithParameters('statamic.cp.users.edit', ['user' => $user->id]);
        app()->instance('request', $request);

        $this->assertEquals([
            'is_current_user' => true,
            'is_enforced' => false,
            'is_setup' => false,
            'can_disable' => true,
            'routes' => [
                'enable' => cp_route('users.two-factor.enable', $user->id),
                'disable' => cp_route('users.two-factor.disable', $user->id),
                'recovery_codes' => [
                    'show' => cp_route('users.two-factor.recovery-codes.show', $user->id),
                    'generate' => cp_route('users.two-factor.recovery-codes.generate', $user->id),
                    'download' => cp_route('users.two-factor.recovery-codes.download', $user->id),
                ],
            ],
        ], $this->fieldtype()->preload());

        $user
            ->set('two_factor_secret', encrypt('secret'))
            ->set('two_factor_confirmed_at', now()->timestamp)
            ->save();
        $this->assertTrue($this->fieldtype()->preload()['is_setup']);

        config()->set('statamic.users.two_factor.enforced_roles', ['*']);
        $this->assertTrue($this->fieldtype()->preload()['is_enforced']);
    }

    #[Test]
    public function it_returns_preload_for_another_user()
    {
        $anotherUser = $this->user();
        $this->actingAs($user = $this->user());

        $request = $this->createRequestWithParameters('statamic.cp.users.edit', ['user' => $anotherUser->id]);
        app()->instance('request', $request);

        $this->assertEquals([
            'is_current_user' => false,
            'is_enforced' => false,
            'is_setup' => false,
            'can_disable' => true,
            'routes' => [
                'enable' => cp_route('users.two-factor.enable', $anotherUser->id),
                'disable' => cp_route('users.two-factor.disable', $anotherUser->id),
                'recovery_codes' => [
                    'show' => cp_route('users.two-factor.recovery-codes.show', $anotherUser->id),
                    'generate' => cp_route('users.two-factor.recovery-codes.generate', $anotherUser->id),
                    'download' => cp_route('users.two-factor.recovery-codes.download', $anotherUser->id),
                ],
            ],
        ], $this->fieldtype()->preload());

        $anotherUser
            ->set('two_factor_secret', encrypt('secret'))
            ->set('two_factor_confirmed_at', now()->timestamp)
            ->save();
        $this->assertTrue($this->fieldtype()->preload()['is_setup']);

        config()->set('statamic.users.two_factor.enforced_roles', ['*']);
        $this->assertTrue($this->fieldtype()->preload()['is_enforced']);
    }

    #[Test]
    public function it_preprocesses_index()
    {
        $user = $this->userWithTwoFactorEnabled();

        $field = $this->fieldtype()->field()->setParent($user);

        $this->assertEquals(
            ['setup' => true],
            $this->fieldtype()->setField($field)->preProcessIndex(null)
        );
    }

    #[Test]
    public function it_preprocesses_index_when_user_has_two_factor_disabled()
    {
        $user = $this->userWithTwoFactorEnabled();
        $user->remove('two_factor_confirmed_at')->save();

        $field = $this->fieldtype()->field()->setParent($user);

        $this->assertEquals(
            ['setup' => false],
            $this->fieldtype()->setField($field)->preProcessIndex(null)
        );
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
            'two_factor_confirmed_at' => now()->timestamp,
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }

    private function createRequestWithParameters(string $routeName, array $parameters = []): Request
    {
        $route = Router::getRoutes()->getByName($routeName);

        $uri = $route->uri;
        $method = $route->methods()[0];
        $fullUrl = route($routeName, $parameters);

        return ($request = Request::create($fullUrl))->setRouteResolver(fn () => (new Route($method, $uri, []))->bind($request));
    }
}
