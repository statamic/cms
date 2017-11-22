<?php

namespace Statamic\Tests\Stache;

use Statamic\API\YAML;
use Statamic\Stache\Drivers\AbstractDriver;
use Statamic\Stache\Drivers\PagesDriver;
use Statamic\Stache\Loader;
use Statamic\Stache\Manager;
use Statamic\Stache\Persister;
use Statamic\Stache\Repository;
use Statamic\API\Config;
use Statamic\Stache\Stache;
use Statamic\Stache\UpdateManager;
use Statamic\Stache\Updater;
use Statamic\Testing\TestCase;
use Statamic\Stache\StacheUpdater;

/**
 * @group stache
 */
class StacheUpdaterTest extends TestCase
{
    /**
     * @var Stache
     */
    private $stache;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var Repository
     */
    private $repo;

    public function setUp()
    {
        parent::setUp();

        $this->stache = $stache = app(Stache::class);
        $driver = new PagesDriver($stache);
        $this->stache->registerDriver($driver);
        $this->repo = $this->stache->repo('pages');
        $this->repo->loaded = true;
        $this->updater = new Updater($this->stache, $driver);
    }

    public function test_that_modified_files_get_added()
    {
        $this->withoutEvents();

        $this->updater->modified([
            'pages/index.md' => '',
            'pages/about/index.md' => ''
        ])->update();

        $this->assertEquals(2, $this->repo->getItems()->count());
    }

    public function test_that_deleted_files_get_removed()
    {
        $this->updater->modified([
            'pages/index.md' => YAML::dump(['id' => '123']),
            'pages/foo/index.md' => YAML::dump(['id' => '234']),
            'pages/bar/index.md' => YAML::dump(['id' => '345'])
        ])->update();

        $this->updater->modified([])->deleted([
            'pages/foo/index.md'
        ])->update();

        $this->assertEquals(2, $this->repo->getItems()->count());
    }
}
