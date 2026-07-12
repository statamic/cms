<?php

namespace Tests\Preferences;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Preferences\Preferences;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PreferencesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        File::delete(resource_path('preferences.yaml'));
        Role::all()->each->delete();
        UserGroup::all()->each->delete();
    }

    #[Test]
    public function it_registers_with_string_and_field_definition()
    {
        $preferences = new Preferences;
        $this->assertEquals([], $preferences->tabs()->all());

        $preferences->register('foo', ['type' => 'text']);

        $this->assertEquals([
            'general' => [
                'display' => 'General',
                'fields' => [
                    'foo' => ['type' => 'text'],
                ],
            ],
        ], $preferences->tabs()->all());
    }

    #[Test]
    public function it_registers_by_returning_array_from_extend_closure()
    {
        $preferences = new Preferences;
        $this->assertEquals([], $preferences->tabs()->all());

        $preferences->extend(function () {
            return [
                'general' => [
                    'fields' => [
                        'foo' => ['type' => 'text'],
                    ],
                ],
            ];
        });

        $preferences->extend(function () {
            return [
                'general' => [
                    'display' => 'Changing the display shouldnt work',
                    'fields' => [
                        'bar' => ['type' => 'text'],
                    ],
                ],
                'more' => [
                    'display' => 'More',
                    'fields' => [
                        'baz' => ['type' => 'text'],
                    ],
                ],
            ];
        });

        $this->assertEquals([], $preferences->tabs()->all());

        $preferences->boot();

        $this->assertEquals([
            'general' => [
                'display' => 'general',
                'fields' => [
                    'foo' => ['type' => 'text'],
                    'bar' => ['type' => 'text'],
                ],
            ],
            'more' => [
                'display' => 'More',
                'fields' => [
                    'baz' => ['type' => 'text'],
                ],
            ],
        ], $preferences->tabs()->all());
    }

    #[Test]
    public function it_defers_registration_until_boot_using_extend_method()
    {
        $preferences = new Preferences;
        $callbackRan = false;

        $preferences->extend(function ($preference) use (&$callbackRan, $preferences) {
            $this->assertEquals($preferences, $preference);
            $callbackRan = true;
        });

        $this->assertFalse($callbackRan);

        $preferences->boot();

        $this->assertTrue($callbackRan);
    }

    #[Test]
    public function it_places_any_preferences_registered_early_without_extend_callback_at_the_end()
    {
        $preferences = new Preferences;

        $preferences->register('one');
        $preferences->register('two');

        $preferences->extend(function ($preference) {
            $preference->register('three');
        });

        $preferences->boot();

        $fields = collect($preferences->tabs()->get('general')['fields'])->keys()->all();

        $this->assertEquals(['three', 'one', 'two'], $fields);
    }

    #[Test]
    public function it_uses_fresh_default_preferences()
    {
        $preferences = new Preferences;

        File::put(resource_path('preferences.yaml'), 'alfa: bravo');

        $this->actingAs(User::make());

        $this->assertEquals(['alfa' => 'bravo'], $preferences->all());

        $preferences->default()->set('charlie', 'delta')->save();

        $this->assertEquals([
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ], $preferences->all());
    }

    #[Test]
    public function it_uses_fresh_role_preferences()
    {
        $preferences = new Preferences;

        $role = tap(Role::make('one')->setPreference('alfa', 'bravo'))->save();

        $this->actingAs(User::make()->assignRole('one'));

        $this->assertEquals(['alfa' => 'bravo'], $preferences->all());

        $role->setPreference('charlie', 'delta')->save();

        $this->assertEquals([
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ], $preferences->all());
    }

    #[Test]
    public function it_uses_fresh_role_preferences_via_group()
    {
        $preferences = new Preferences;

        tap(Role::make('role')->setPreference('alfa', 'bravo'))->save();
        tap(UserGroup::make()->handle('group')->assignRole('role'))->save();

        $this->actingAs(User::make()->addToGroup('group'));

        $this->assertEquals(['alfa' => 'bravo'], $preferences->all());
    }

    #[Test]
    public function it_uses_fresh_user_preferences()
    {
        $preferences = new Preferences;

        $user = User::make()->setPreference('alfa', 'bravo');

        $this->actingAs($user);

        $this->assertEquals(['alfa' => 'bravo'], $preferences->all());

        $user->setPreference('charlie', 'delta')->save();

        $this->assertEquals([
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ], $preferences->all());
    }

    #[Test]
    public function it_uses_preferences_with_priority()
    {
        // This test will check a handful of preferences, adding them step by step. With each step up in priority, fewer
        // values are being added. This is so that the assertions can demonstrate that the lower level preferences are
        // still being applied. i.e. defaults sets 8 prefs, group2-role2 sets 7, and so on. The final assertion will
        // be able to show that all 8 preferences have been applied from all the different priority levels.

        $preferences = new Preferences;

        $this->actingAs($user = User::make());
        $this->assertEquals([], $preferences->all());

        // Defaults get applied first.

        $preferences->default()->merge([
            'alfa' => $value = 'default',
            'bravo' => $value,
            'charlie' => $value,
            'delta' => $value,
            'echo' => $value,
            'foxtrot' => $value,
            'golf' => $value,
            'hotel' => $value,
        ])->save();

        $this->assertEquals([
            'alfa' => 'default',
            'bravo' => 'default',
            'charlie' => 'default',
            'delta' => 'default',
            'echo' => 'default',
            'foxtrot' => 'default',
            'golf' => 'default',
            'hotel' => 'default',
        ], $preferences->all());

        // Preferences get applied from roles through groups, and the order of the roles in the group matters.

        tap(Role::make($handle = 'first_role_in_first_group')->setPreferences([
            'alfa' => $handle,
            'bravo' => $handle,
            'charlie' => $handle,
            'delta' => $handle,
        ]))->save();
        tap(Role::make($handle = 'second_role_in_first_group')->setPreferences([
            'alfa' => $handle,
            'bravo' => $handle,
            'charlie' => $handle,
            'delta' => $handle,
            'echo' => $handle,
        ]))->save();
        tap(Role::make($handle = 'first_role_in_second_group')->setPreferences([
            'alfa' => $handle,
            'bravo' => $handle,
            'charlie' => $handle,
            'delta' => $handle,
            'echo' => $handle,
            'foxtrot' => $handle,
        ]))->save();
        tap(Role::make($handle = 'second_role_in_second_group')->setPreferences([
            'alfa' => $handle,
            'bravo' => $handle,
            'charlie' => $handle,
            'delta' => $handle,
            'echo' => $handle,
            'foxtrot' => $handle,
            'golf' => $handle,
        ]))->save();
        tap(UserGroup::make()->handle('first_group')->assignRole('first_role_in_first_group')->assignRole('second_role_in_first_group'))->save();
        tap(UserGroup::make()->handle('second_group')->assignRole('first_role_in_second_group')->assignRole('second_role_in_second_group'))->save();
        $user->addToGroup('first_group')->addToGroup('second_group')->save();

        $this->assertEquals([
            'alfa' => 'first_role_in_first_group',
            'bravo' => 'first_role_in_first_group',
            'charlie' => 'first_role_in_first_group',
            'delta' => 'first_role_in_first_group',
            'echo' => 'second_role_in_first_group',
            'foxtrot' => 'first_role_in_second_group',
            'golf' => 'second_role_in_second_group',
            'hotel' => 'default',
        ], $preferences->all());

        // Roles applied directly to the user take priority over those applied through groups.

        tap(Role::make($handle = 'first_direct_role')->setPreferences([
            'alfa' => $handle,
            'bravo' => $handle,
        ]))->save();
        tap(Role::make($handle = 'second_direct_role')->setPreferences([
            'alfa' => $handle,
            'bravo' => $handle,
            'charlie' => $handle,
        ]))->save();
        $user->assignRole('first_direct_role')->assignRole('second_direct_role')->save();

        $this->assertEquals([
            'alfa' => 'first_direct_role',
            'bravo' => 'first_direct_role',
            'charlie' => 'second_direct_role',
            'delta' => 'first_role_in_first_group',
            'echo' => 'second_role_in_first_group',
            'foxtrot' => 'first_role_in_second_group',
            'golf' => 'second_role_in_second_group',
            'hotel' => 'default',
        ], $preferences->all());

        // User preferences take priority over roles.

        $user->setPreference('alfa', 'user')->save();

        $this->assertEquals([
            'alfa' => 'user',
            'bravo' => 'first_direct_role',
            'charlie' => 'second_direct_role',
            'delta' => 'first_role_in_first_group',
            'echo' => 'second_role_in_first_group',
            'foxtrot' => 'first_role_in_second_group',
            'golf' => 'second_role_in_second_group',
            'hotel' => 'default',
        ], $preferences->all());
    }
}
