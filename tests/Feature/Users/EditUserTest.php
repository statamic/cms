<?php

namespace Tests\Feature\Users;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('2fa')]
class EditUserTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_form()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit users']]);
        $user = tap(User::make()->email('test@domain.com')->set('name', 'Johh Smith'))->save();
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAs($me)
            ->get($user->editUrl())
            ->assertOk()
            ->assertViewHas('title', 'test@domain.com');
    }

    #[Test]
    public function it_provides_2fa_data()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit users']]);
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAs($me)
            ->get($me->editUrl())
            ->assertOk()
            ->assertViewHasAll([
                'twoFactor.isEnforced',
                'twoFactor.wasSetup',
                'twoFactor.routes.enable',
                'twoFactor.routes.disable',
                'twoFactor.routes.recoveryCodes.show',
                'twoFactor.routes.recoveryCodes.generate',
                'twoFactor.routes.recoveryCodes.download',
            ]);
    }

    #[Test]
    public function it_doesnt_provide_2fa_data_when_editing_someone_else()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit users']]);
        $user = tap(User::make()->email('test@domain.com')->set('name', 'John Smith'))->save();
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAs($me)
            ->get($user->editUrl())
            ->assertOk()
            ->assertViewHas('twoFactor', fn ($twoFactor) => $twoFactor === null);
    }
}
