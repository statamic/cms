<?php

namespace Tests\Auth;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\AugmentedUser;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Tests\Data\AugmentedTestCase;
use Tests\FakesRoles;
use Tests\FakesUserGroups;

class AugmentedUserTest extends AugmentedTestCase
{
    use FakesRoles;
    use FakesUserGroups;

    #[Test]
    public function it_gets_values()
    {
        Carbon::setTestNow('2020-04-15 13:00:00');

        $blueprint = User::blueprint();
        $contents = $blueprint->contents();
        $contents['tabs']['main']['sections'][0]['fields'] = array_merge($contents['tabs']['main']['sections'][0]['fields'], [
            ['handle' => 'two', 'field' => ['type' => 'text']],
            ['handle' => 'four', 'field' => ['type' => 'text']],
            ['handle' => 'unused_in_bp', 'field' => ['type' => 'text']],
        ]);
        $blueprint->setContents($contents);
        Blueprint::shouldReceive('find')->with('user')->andReturn($blueprint);

        $this->setTestRoles([
            'role_one' => [],
            'role_two' => [],
        ]);
        $this->setTestUserGroups([
            'group_one' => [],
            'group_two' => [],
        ]);

        $user = tap(User::make()
            ->id('user-id')
            ->data([
                'name' => 'John Smith',
                'one' => 'the "one" value on the user',
                'two' => 'the "two" value on the user and in the blueprint',
            ])
            ->email('john@example.com')
            ->assignRole('role_one')
            ->addToGroup('group_one')
            ->setSupplement('three', 'the "three" value supplemented on the user')
            ->setSupplement('four', 'the "four" value supplemented on the user and in the blueprint')
            ->setPreferredLocale('en')
        )->save();

        $user->setMeta('last_login', '1486131000');

        $augmented = new AugmentedUser($user);

        $expectations = [
            'id' => ['type' => 'string', 'value' => 'user-id'],
            'name' => ['type' => 'string', 'value' => 'John Smith'],
            'title' => ['type' => 'string', 'value' => 'john@example.com'],
            'email' => ['type' => 'string', 'value' => 'john@example.com'],
            'initials' => ['type' => 'string', 'value' => 'JS'],
            'edit_url' => ['type' => 'string', 'value' => 'http://localhost/cp/users/user-id/edit'],
            'is_user' => ['type' => 'bool', 'value' => true],
            'last_login' => ['type' => Carbon::class, 'value' => '2017-02-03 14:10'],
            'avatar' => ['type' => 'string', 'value' => null],
            'api_url' => ['type' => 'string', 'value' => 'http://localhost/api/users/user-id'],
            'preferred_locale' => ['type' => 'string', 'value' => 'en'],

            'roles' => ['type' => 'array', 'value' => ['role_one']],
            'is_role_one' => ['type' => 'bool', 'value' => true],
            'is_role_two' => ['type' => 'bool', 'value' => false],

            'groups' => ['type' => 'array', 'value' => ['group_one']],
            'in_group_one' => ['type' => 'bool', 'value' => true],
            'in_group_two' => ['type' => 'bool', 'value' => false],

            'one' => ['type' => 'string', 'value' => 'the "one" value on the user'],
            'two' => ['type' => 'string', 'value' => 'the "two" value on the user and in the blueprint'],
            'three' => ['type' => 'string', 'value' => 'the "three" value supplemented on the user'],
            'four' => ['type' => 'string', 'value' => 'the "four" value supplemented on the user and in the blueprint'],
            'unused_in_bp' => ['type' => 'string', 'value' => null],
        ];

        $this->assertAugmentedCorrectly($expectations, $augmented);
    }
}
