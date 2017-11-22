<?php

namespace Statamic\Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Repository;
use Statamic\Stache\Drivers\PagesDriver;
use Statamic\Stache\Drivers\EntriesDriver;

/**
 * @group stache
 * @group stacheunits
 */
class StacheTest extends TestCase
{
    /**
     * @var Stache
     */
    protected $stache;

    public function setUp()
    {
        parent::setUp();

        $this->stache = app(Stache::class);
        $this->stache->locales(['en', 'fr']);

        $this->stache->registerDriver(new PagesDriver($this->stache));
        $this->stache->registerDriver(new EntriesDriver($this->stache));
    }

    public function test_that_all_ids_can_be_retrieved()
    {
        $this->addSamplePathsToStache();

        $expected = [
            '123' => 'pages::pages/one/index.md',
            '234' => 'pages::pages/two/index.md',
            '345' => 'entries/blog::collections/blog/post.md',
            '456' => 'entries/blog::collections/blog/post2.md'
        ];

        $this->assertEquals($expected, $this->stache->ids()->all());
    }

    public function test_that_all_uris_can_be_retrieved()
    {
        $repo = $this->stache->repo('pages');
        $repo->loaded = true; // prevent filesystem loads
        $repo->setUris(['1' => '/one', '2' => '/two', '3' => '/three']);
        $repo->setUris(['1' => '/un', '2' => '/deux'], 'fr');

        $expected = [
            'en::/one' => '1',
            'en::/two' => '2',
            'en::/three' => '3',
            'fr::/un' => '1',
            'fr::/deux' => '2'
        ];

        $this->assertEquals($expected, $this->stache->uris()->all());
    }

    private function addSamplePathsToStache()
    {
        $repo = $this->stache->repo('pages');
        $repo->loaded = true; // prevent filesystem loads
        $repo->setPaths(['123' => 'pages/one/index.md', '234' => 'pages/two/index.md']);

        $repo2 = $this->stache->repo('entries');
        $repo2->loaded = true;
        $repo2->setPaths(['blog::345' => 'collections/blog/post.md', 'blog::456' => 'collections/blog/post2.md']);
    }
}