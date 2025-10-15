<?php

namespace Tests\Feature\Users;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_saves_a_user()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit users']]);
        $user = tap(User::make()->email('test@domain.com')->set('name', 'Johh Smith'))->save();
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this
            ->actingAs($me)
            ->patch($user->updateUrl(), [
                'email' => 'updated@domain.com',
                'name' => 'Jonathan Smith',
            ])
            ->assertOk();

        $this->assertEquals('updated@domain.com', User::find($user->id())->email());
        $this->assertEquals('Jonathan Smith', User::find($user->id())->name);
    }

    #[Test]
    public function super_users_can_promote_others_to_super()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit users']]);
        $user = tap(User::make()->email('test@domain.com'))->save();
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test')->makeSuper())->save();

        $this->assertFalse(User::find($user->id())->isSuper());

        $this
            ->actingAs($me)
            ->patch($user->updateUrl(), [
                'email' => 'test@domain.com',
                'super' => true,
            ])
            ->assertOk();

        $this->assertTrue(User::find($user->id())->isSuper());
    }

    #[Test]
    public function non_super_users_cannot_promote_others_to_super()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit users']]);
        $user = tap(User::make()->email('test@domain.com'))->save();
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this->assertFalse(User::find($user->id())->isSuper());

        $this
            ->actingAs($me)
            ->patch($user->updateUrl(), [
                'email' => 'test@domain.com',
                'super' => true,
            ])
            ->assertOk();

        $this->assertFalse(User::find($user->id())->isSuper());
    }

    #[Test]
    public function super_users_can_demote_other_from_super()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit users']]);
        $user = tap(User::make()->email('test@domain.com')->makeSuper())->save();
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test')->makeSuper())->save();

        $this->assertTrue(User::find($user->id())->isSuper());

        $this
            ->actingAs($me)
            ->patch($user->updateUrl(), [
                'email' => 'test@domain.com',
                'super' => false,
            ])
            ->assertOk();

        $this->assertFalse(User::find($user->id())->isSuper());
    }

    #[Test]
    public function non_super_users_cannot_demote_others_from_super()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit users']]);
        $user = tap(User::make()->email('test@domain.com')->makeSuper())->save();
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();

        $this->assertTrue(User::find($user->id())->isSuper());

        $this
            ->actingAs($me)
            ->patch($user->updateUrl(), [
                'email' => 'test@domain.com',
                'super' => false,
            ])
            ->assertOk();

        $this->assertTrue(User::find($user->id())->isSuper());
    }
}
