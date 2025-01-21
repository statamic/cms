<?php

namespace Tests\CP;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\User;
use Statamic\Statamic;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AuthRedirectTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        Statamic::pushCpRoutes(function () {
            Route::get('hammertime', function () {
                throw new AuthorizationException("Can't touch this.");
            });
        });
    }

    #[Test]
    public function it_redirects_back_to_referrer()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->from('/original')
            ->get('/cp/hammertime')
            ->assertRedirect('/original')
            ->assertSessionHas(['error' => "Can't touch this."]);
    }

    #[Test]
    public function it_redirects_to_cp_index_without_referrer()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get('/cp/hammertime')
            ->assertRedirect(cp_route('index'))
            ->assertSessionHas(['error' => "Can't touch this."]);
    }

    #[Test]
    public function it_redirects_somewhere_if_the_referrer_was_the_login_page()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->from(cp_route('login'))
            ->get('/cp/hammertime')
            ->assertRedirect(cp_route('index'))
            ->assertSessionHas(['error' => "Can't touch this."]);
    }

    #[Test]
    public function it_redirects_to_unauthorized_view_if_there_would_be_a_redirect_loop()
    {
        $this->setTestRoles(['undashboardable' => ['access cp']]);
        $user = tap(User::make()->assignRole('draft_viewer'))->save();

        $this
            ->actingAs($user)
            ->get('/cp')
            ->assertRedirect(cp_route('unauthorized'))
            ->assertSessionHas(['error' => 'Unauthorized.']);
    }
}
