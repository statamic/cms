<?php

namespace CP;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\Facades\Preference;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StartPageTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(resource_path('preferences.yaml'));
    }

    #[Test]
    public function it_uses_start_page_preference()
    {
        config('statamic.cp.start_page', 'dashboard');

        Preference::default()->setPreference('start_page', 'collections/pages')->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get('/cp')
            ->assertRedirect('/cp/collections/pages');
    }

    #[Test]
    public function it_falls_back_to_start_page_config_option_when_preference_is_missing()
    {
        config('statamic.cp.start_page', 'dashboard');

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get('/cp')
            ->assertRedirect('/cp/dashboard');
    }
}
