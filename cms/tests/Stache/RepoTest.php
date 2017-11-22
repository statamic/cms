<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\Stache\Repository;

/**
 * @group stache
 * @group stacheunits
 */
class RepoTest extends TestCase
{
    /**
     * @var Repository
     */
    protected $repo;

    public function setUp()
    {
        parent::setUp();

        $this->withoutEvents();

        $this->repo = new Repository;
        $this->repo->loaded = true; // prevent filesystem loading
    }

    public function test_that_uris_can_be_get_and_set()
    {
        $this->repo->setUri('1', '/one');

        $this->assertEquals('/one', $this->repo->getUri('1'));
    }

    public function test_that_uris_can_be_get_and_set_on_specific_locales()
    {
        $this->repo->setUri('1', '/one');
        $this->repo->setUri('1', '/un', 'fr');

        $this->assertEquals('/one', $this->repo->getUri('1'));
        $this->assertEquals('/un', $this->repo->getUri('1', 'fr'));
    }

    public function test_that_paths_can_be_get_and_set()
    {
        $this->repo->setPath('1', 'one.md');

        $this->assertEquals('one.md', $this->repo->getPath('1'));
    }

    public function test_that_paths_can_be_get_and_set_on_specific_locales()
    {
        $this->repo->setPath('1', 'one.md');
        $this->repo->setPath('1', 'fr.one.md', 'fr');

        $this->assertEquals('one.md', $this->repo->getPath('1'));
        $this->assertEquals('fr.one.md', $this->repo->getPath('1', 'fr'));
    }

    public function test_that_uris_can_be_get_and_set_in_bulk()
    {
        $this->repo->setUris(['1' => '/one', '2' => '/two']);

        $this->assertEquals(['1' => '/one', '2' => '/two'], $this->repo->getUris()->all());
    }

    public function test_that_uris_can_be_get_and_set_in_bulk_for_specific_locales()
    {
        $this->repo->setUris(['1' => '/one', '2' => '/two']);
        $this->repo->setUris(['1' => '/un', '2' => '/deux'], 'fr');

        $this->assertEquals(['1' => '/one', '2' => '/two'], $this->repo->getUris()->all());
        $this->assertEquals(['1' => '/un', '2' => '/deux'], $this->repo->getUris('fr')->all());
    }

    public function test_that_paths_can_be_get_and_set_in_bulk()
    {
        $this->repo->setPaths(['1' => 'one.md', '2' => 'two.md']);

        $this->assertEquals(['1' => 'one.md', '2' => 'two.md'], $this->repo->getPaths()->all());
    }

    public function test_that_paths_can_be_get_and_set_in_bulk_for_specific_locales()
    {
        $this->repo->setPaths(['1' => 'one.md', '2' => 'two.md']);
        $this->repo->setPaths(['1' => 'fr.one.md', '2' => 'fr.two.md'], 'fr');

        $this->assertEquals(['1' => 'one.md', '2' => 'two.md'], $this->repo->getPaths()->all());
        $this->assertEquals(['1' => 'fr.one.md', '2' => 'fr.two.md'], $this->repo->getPaths('fr')->all());
    }

    public function test_that_items_can_be_get_and_set()
    {
        $thing = new \stdClass();

        $this->repo->setItem('1', $thing);

        $this->assertEquals($thing, $this->repo->getItem('1'));
    }

    public function test_that_items_can_be_get_and_set_in_bulk()
    {
        $thing1 = new \stdClass();
        $thing1->foo = 'bar';

        $thing2 = new \stdClass();
        $thing2->foo = 'baz';

        $this->repo->setItems(['1' => $thing1, '2' => $thing2]);

        $this->assertEquals(['1' => $thing1, '2' => $thing2], $this->repo->getItems()->all());
    }

    public function test_that_removing_an_item_also_removes_uris_and_paths()
    {
        $thing1 = new \stdClass();
        $thing1->foo = 'bar';

        $thing2 = new \stdClass();
        $thing2->foo = 'baz';

        $this->repo->setItems(['1' => $thing1, '2' => $thing2]);
        $this->repo->setUris(['1' => '/one', '2' => '/two']);
        $this->repo->setPaths(['1' => 'one.md', '2' => 'two.md']);

        $this->repo->removeItem('1');

        $this->assertEquals(1, $this->repo->getUris()->count());
        $this->assertEquals(1, $this->repo->getPaths()->count());
        $this->assertEquals(1, $this->repo->getItems()->count());
    }

    public function test_that_ids_can_be_retrieved_by_path()
    {
        $this->repo->setPath('1', 'one.md');

        $this->assertEquals('1', $this->repo->getIdByPath('one.md'));
    }

    public function test_that_ids_can_be_retrieved_by_uri()
    {
        $this->repo->setPath('1', '/one');

        $this->assertEquals('1', $this->repo->getIdByPath('/one'));
    }
}