<?php

namespace Tests\Preferences;

use Statamic\Facades\File;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Facades\User;
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
