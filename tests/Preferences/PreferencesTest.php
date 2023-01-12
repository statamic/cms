<?php

namespace Tests\Preferences;

use Facades\Statamic\Preferences\CorePreferences;
use Statamic\Facades\File;
use Statamic\Facades\Preference;
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
        $this->assertEquals([], Preference::sections()->all());

        Preference::register('foo', ['type' => 'text']);

        $this->assertEquals([
            'general' => [
                'display' => 'General',
                'fields' => [
                    'foo' => ['type' => 'text'],
                ],
            ],
        ], Preference::sections()->all());
    }

    /** @test */
    public function it_registers_by_returning_array_from_extend_closure()
    {
        // Avoid adding core preferences to make test simpler.
        CorePreferences::shouldReceive('boot')->andReturnNull();

        $this->assertEquals([], Preference::sections()->all());

        Preference::extend(function () {
            return [
                'general' => [
                    'fields' => [
                        'foo' => ['type' => 'text'],
                    ],
                ],
            ];
        });

        $this->assertEquals([], Preference::sections()->all());

        Preference::boot();

        $this->assertEquals([
            'general' => [
                'display' => 'general',
                'fields' => [
                    'foo' => ['type' => 'text'],
                ],
            ],
        ], Preference::sections()->all());
    }

    /** @test */
    public function it_defers_registration_until_boot_using_extend_method()
    {
        $callbackRan = false;

        Preference::extend(function ($preference) use (&$callbackRan) {
            $this->assertEquals(Preference::getFacadeRoot(), $preference);
            $callbackRan = true;
        });

        $this->assertFalse($callbackRan);

        Preference::boot();

        $this->assertTrue($callbackRan);
    }

    /** @test */
    public function it_places_any_preferences_registered_early_without_extend_callback_at_the_end()
    {
        // Avoid adding core preferences to make test simpler.
        CorePreferences::shouldReceive('boot')->andReturnNull();

        Preference::register('one');
        Preference::register('two');

        Preference::extend(function ($preference) {
            $preference->register('three');
        });

        Preference::boot();

        $fields = collect(Preference::sections()->get('general')['fields'])->keys()->all();

        $this->assertEquals(['three', 'one', 'two'], $fields);
    }

    /** @test */
    public function it_uses_fresh_default_preferences()
    {
        File::put(resource_path('preferences.yaml'), 'alfa: bravo');

        $this->actingAs(User::make());

        $this->assertEquals(['alfa' => 'bravo'], Preference::all());

        Preference::default()->set('charlie', 'delta')->save();

        $this->assertEquals([
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ], Preference::all());
    }

    /** @test */
    public function it_uses_fresh_role_preferences()
    {
        $role = tap(Role::make('one')->setPreference('alfa', 'bravo'))->save();

        $this->actingAs(User::make()->assignRole('one'));

        $this->assertEquals(['alfa' => 'bravo'], Preference::all());

        $role->setPreference('charlie', 'delta')->save();

        $this->assertEquals([
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ], Preference::all());
    }

    /** @test */
    public function it_uses_fresh_user_preferences()
    {
        $user = User::make()->setPreference('alfa', 'bravo');

        $this->actingAs($user);

        $this->assertEquals(['alfa' => 'bravo'], Preference::all());

        $user->setPreference('charlie', 'delta')->save();

        $this->assertEquals([
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ], Preference::all());
    }
}
