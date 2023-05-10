<?php

namespace Tests\Preferences;

use Statamic\Facades\File;
use Statamic\Facades\Role;
use Statamic\Facades\User;
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
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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
}
