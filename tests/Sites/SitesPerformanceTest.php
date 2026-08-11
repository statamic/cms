<?php

namespace Tests\Sites;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
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
}
