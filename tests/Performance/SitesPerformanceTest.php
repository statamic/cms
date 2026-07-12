<?php

namespace Tests\Performance;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Sites\Site;
use Statamic\Sites\Sites;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SitesPerformanceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /**
     * Test that super users bypass authorization filtering for all sites.
     * This ensures the performance optimization for super users is working.
     */
    #[Test]
    public function super_user_bypasses_site_authorization_check()
    {
        $sites = (new Sites)->setSites([
            'en' => ['url' => 'http://test.com/'],
            'fr' => ['url' => 'http://fr.test.com/'],
            'de' => ['url' => 'http://test.com/de/'],
        ]);

        // Create and act as a super user
        $superUser = tap(User::make()->id(1))->save();
        $superUser->makeSuper()->save();
        $this->actingAs($superUser);

        // Super user should get all sites without any filtering
        $authorizedSites = $sites->authorized();

        $this->assertEquals(3, $authorizedSites->count());
        $this->assertEquals(['en', 'fr', 'de'], $authorizedSites->keys()->all());
    }

    /**
     * Test that regular users still get filtered authorization check.
     * This ensures regular users only see sites they have access to.
     */
    #[Test]
    public function regular_user_gets_filtered_site_authorization()
    {
        $sites = (new Sites)->setSites([
            'en' => ['url' => 'http://test.com/'],
            'fr' => ['url' => 'http://fr.test.com/'],
            'de' => ['url' => 'http://test.com/de/'],
        ]);

        Role::make('editor')
            ->permissions([
                'access en site',
                'access fr site',
            ])
            ->save();

        $regularUser = tap(User::make()->assignRole('editor'))->save();
        $this->actingAs($regularUser);

        \Statamic\Facades\Site::shouldReceive('multiEnabled')->andReturnTrue();
        \Statamic\Facades\Site::shouldReceive('all')->andReturn(collect()); // CorePermissions calls this

        // Regular user should only get sites they have access to
        $authorizedSites = $sites->authorized();

        $this->assertEquals(2, $authorizedSites->count());
        $this->assertEquals(['en', 'fr'], $authorizedSites->keys()->all());
    }

    /**
     * Test that site configuration with plain strings doesn't trigger Antlers parsing.
     * This ensures performance optimization for non-template string values.
     */
    #[Test]
    public function plain_string_site_config_skips_antlers_parsing()
    {
        // Test with a simple string URL that has no template syntax
        $site = new Site('en', [
            'url' => '/en/',
            'locale' => 'en_US',
            'name' => 'English',
        ]);

        // These should be plain strings, not parsed by Antlers
        // Note: URLs are tidied, so trailing slash is removed
        $this->assertEquals('/en', $site->url());
        $this->assertEquals('en_US', $site->locale());
        $this->assertEquals('English', $site->name());
    }

    /**
     * Test that site configuration with Antlers syntax still gets parsed.
     * This ensures the optimization doesn't break actual template parsing.
     */
    #[Test]
    public function site_config_with_antlers_syntax_gets_parsed()
    {
        // Test with template syntax that should be parsed
        $site = new Site('en', [
            'url' => '/',
            'locale' => '{{ config:app.locale }}',
            'name' => 'Test Site',
        ]);

        // Locale should have been parsed
        $this->assertNotEmpty($site->locale());
        // It should not contain the template syntax anymore
        $this->assertStringNotContainsString('{{', $site->locale());
    }
}
