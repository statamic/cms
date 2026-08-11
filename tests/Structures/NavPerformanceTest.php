<?php

namespace Tests\Structures;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UnlinksPaths;

class NavPerformanceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use UnlinksPaths;

    private $directory;

    public function setUp(): void
    {
        parent::setUp();

        $stache = $this->app->make('stache');
        $stache->store('nav-trees')->directory($this->directory = $this->fakeStacheDirectory.$this->directory.'');

        $this->setSites([
            'en' => ['locale' => 'en_US', 'url' => '/'],
            'fr' => ['locale' => 'fr_FR', 'url' => '/fr/'],
            'de' => ['locale' => 'de_DE', 'url' => '/de/'],
        ]);
    }

    /**
     * Test that existsIn() efficiently checks for nav in a specific site.
     * This verifies the performance optimization that uses in() instead of trees().
     */
    #[Test]
    public function exists_in_checks_nav_for_specific_site()
    {
        $nav = tap(Nav::make('links'))->save();

        // Create a tree for only the 'en' site
        $nav->makeTree('en', [['title' => 'Home', 'url' => '/']])->save();

        // Nav should exist in 'en' site
        $this->assertTrue($nav->existsIn('en'));

        // Nav should not exist in 'fr' site
        $this->assertFalse($nav->existsIn('fr'));

        // Nav should not exist in 'de' site
        $this->assertFalse($nav->existsIn('de'));
    }

    /**
     * Test that existsIn() works when nav exists in multiple sites.
     */
    #[Test]
    public function exists_in_checks_nav_across_multiple_sites()
    {
        $nav = tap(Nav::make('links'))->save();

        // Create trees for 'en' and 'fr' sites
        $nav->makeTree('en', [['title' => 'Home', 'url' => '/']])->save();
        $nav->makeTree('fr', [['title' => 'Accueil', 'url' => '/']])->save();

        // Nav should exist in both 'en' and 'fr' sites
        $this->assertTrue($nav->existsIn('en'));
        $this->assertTrue($nav->existsIn('fr'));

        // Nav should not exist in 'de' site
        $this->assertFalse($nav->existsIn('de'));
    }

    /**
     * Test that existsIn() stays bounded to currently registered sites,
     * even if a tree file exists on disk for a site that's since been removed.
     */
    #[Test]
    public function exists_in_ignores_trees_for_sites_that_are_no_longer_registered()
    {
        $nav = tap(Nav::make('links'))->save();

        $nav->makeTree('de', [['title' => 'Startseite', 'url' => '/']])->save();

        $this->setSites([
            'en' => ['locale' => 'en_US', 'url' => '/'],
            'fr' => ['locale' => 'fr_FR', 'url' => '/fr/'],
        ]);

        $this->assertFalse($nav->existsIn('de'));
    }
}
