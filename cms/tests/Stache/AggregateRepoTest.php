<?php

namespace Statamic\Tests\Stache;

use Statamic\API\YAML;
use Statamic\Stache\AggregateRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\StacheUpdater;
use Statamic\Testing\TestCase;

/**
 * @group stache
 * @group stacheunits
 */
class AggregateRepoTest extends TestCase
{
    /**
     * @var AggregateRepository
     */
    protected $repo;

    public function setUp()
    {
        parent::setUp();

        $this->withoutEvents();

        $this->repo = new AggregateRepository('test');
    }

    public function test_that_sub_repos_can_set_paths()
    {
        $this->repo->setPath('a::one', 'foo');
        $this->assertEquals('foo', $this->repo->repo('a')->getPath('one'));
    }

    public function test_that_sub_repos_can_set_uris()
    {
        $this->repo->setUri('b::two', 'bar');
        $this->assertEquals('bar', $this->repo->repo('b')->getUri('two'));
    }

    public function test_that_sub_repos_can_set_items()
    {
        $this->repo->setItem('c::three', 'baz');

        // mark it as loaded so we dont need to retrieve from file
        $this->repo->repo('c')->loaded = true;

        $this->assertEquals('baz', $this->repo->repo('c')->getItem('three'));
    }

    public function test_that_sub_repos_can_get_paths()
    {
        $this->repo->setPath('a::one', 'foo');

        $this->assertEquals('foo', $this->repo->getPath('a::one'));
    }

    public function test_that_sub_repos_can_get_uris()
    {
        $this->repo->setUri('b::two', 'bar');

        $this->assertEquals('bar', $this->repo->getUri('b::two'));
    }

    public function test_that_sub_repos_can_get_items()
    {
        $this->repo->setItem('c::three', 'baz');

        // mark it as loaded so we dont need to retrieve from file
        $this->repo->repo('c')->loaded = true;

        $this->assertEquals('baz', $this->repo->getItem('c::three'));
    }

    public function test_that_getting_a_value_from_a_non_existing_repo_returns_null()
    {
        $this->repo->setPath('a::one', 'foo');
        $this->repo->setUri('b::two', 'bar');
        $this->repo->setPath('c::three', 'baz');

        // mark it as loaded so we dont need to retrieve from file
        $this->repo->repo('c')->loaded = true;

        $this->assertNull($this->repo->getPath('x::one'));
        $this->assertNull($this->repo->getUri('y::two'));
        $this->assertNull($this->repo->getItem('z::three'));
    }

    public function test_that_it_gets_paths_gets_from_all_repos()
    {
        $this->repo->setPath('a::one', 'foo');
        $this->repo->setPath('b::two', 'bar');

        $expected = [
            'a' => ['one' => 'foo'],
            'b' => ['two' => 'bar'],
        ];

        $this->assertEquals($expected, $this->repo->getPaths()->toArray());
    }

    public function test_that_it_gets_uris_from_all_repos()
    {
        $this->repo->setUri('a::one', 'foo');
        $this->repo->setUri('b::two', 'bar');

        $expected = [
            'a' => ['one' => 'foo'],
            'b' => ['two' => 'bar'],
        ];

        $this->assertEquals($expected, $this->repo->getUris()->toArray());
    }

    public function test_that_it_gets_items_from_all_repos()
    {
        $this->repo->setItem('a::one', 'foo');
        $this->repo->setItem('b::two', 'bar');

        // mark it as loaded so we dont need to retrieve from file
        $this->repo->repo('a')->loaded = true;
        $this->repo->repo('b')->loaded = true;

        $expected = [
            'a' => ['one' => 'foo'],
            'b' => ['two' => 'bar'],
        ];

        $this->assertEquals($expected, $this->repo->getItems()->toArray());
    }

    public function test_that_keys_without_dots_throw_exception()
    {
        $this->expectException('Exception');

        $this->repo->getPath('foo');
    }

    public function test_that_paths_can_be_set_for_sub_repos_at_one_time()
    {
        $this->repo->setPaths([
            'a::one' => 'foo',
            'b::two' => 'bar'
        ]);

        $this->assertEquals(['one' => 'foo'], $this->repo->repo('a')->getPaths()->all());
        $this->assertEquals(['two' => 'bar'], $this->repo->repo('b')->getPaths()->all());
    }

    public function test_that_uris_can_be_set_for_sub_repos_at_one_time()
    {
        $this->repo->setUris([
            'a::one' => 'foo',
            'b::two' => 'bar'
        ]);

        $this->assertEquals(['one' => 'foo'], $this->repo->repo('a')->getUris()->all());
        $this->assertEquals(['two' => 'bar'], $this->repo->repo('b')->getUris()->all());
    }

    public function test_that_items_can_be_set_for_sub_repos_at_one_time()
    {
        $this->repo->setItems([
            'a::one' => 'foo',
            'b::two' => 'bar'
        ]);

        // mark it as loaded so we dont need to retrieve from file
        $this->repo->repo('a')->loaded = true;
        $this->repo->repo('b')->loaded = true;

        $this->assertEquals(['one' => 'foo'], $this->repo->repo('a')->getItems()->all());
        $this->assertEquals(['two' => 'bar'], $this->repo->repo('b')->getItems()->all());
    }

    public function test_that_ids_can_be_retrieved()
    {
        $this->repo->setPaths([
            'a::one' => 'foo',
            'b::two' => 'bar'
        ]);

        $this->assertEquals(['one' => 'one', 'two' => 'two'], $this->repo->getIds()->all());
    }

    public function test_that_a_repo_key_can_be_found_by_item_id()
    {
        $this->repo->setPaths([
            'a::one' => 'foo',
            'b::two' => 'bar'
        ]);

        $this->assertEquals('b', $this->repo->getRepoKeyById('two'));
    }

    public function test_that_a_repo_can_be_found_by_item_id()
    {
        $this->repo->setPaths([
            'a::one' => 'foo',
            'b::two' => 'bar'
        ]);

        $this->assertEquals('b', $this->repo->getRepoById('two')->key());
    }

    public function test_that_a_sub_repo_item_id_can_be_found_by_path()
    {
        $this->repo->setPaths([
            'a::one' => 'foo',
            'b::two' => 'bar'
        ]);

        $this->assertEquals('two', $this->repo->getIdByPath('b::bar'));
    }
}