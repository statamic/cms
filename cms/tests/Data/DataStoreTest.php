<?php

namespace Statamic\Tests;

use Statamic\Data\DataStore;
use Statamic\Testing\TestCase;

class DataStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataStore
     */
    protected $store;

    public function setUp()
    {
        parent::setUp();

        $this->store = new DataStore('en');
    }

    public function test_sets_and_gets_data()
    {
        $this->store->set('foo', 'bar');

        $this->assertEquals('bar', $this->store->get('foo'));
    }

    public function test_sets_and_gets_data_on_locale()
    {
        $this->store->targetLocale('fr')->set('foo', 'le foo');

        $this->assertEquals('le foo', $this->store->get('foo'));
    }

    public function test_that_all_locales_can_be_retrieved()
    {
        $this->store->targetLocale('en')->set('foo', 'bar');
        $this->store->targetLocale('fr')->set('foo', 'le bar');

        $this->assertArraySubset(['en', 'fr'], $this->store->locales());
        $this->assertArrayNotHasKey('de', $this->store->locales());
    }

    public function test_that_getting_all_data_merges_with_default_locale()
    {
        $this->store->targetLocale('en');
        $this->store->set('foo', 'bar');
        $this->store->set('hello', 'world');

        $this->store->targetLocale('fr');
        $this->store->set('foo', 'le bar');

        $expected = [
            'foo' => 'le bar',
            'hello' => 'world'
        ];

        $this->assertEquals($expected, $this->store->all());
    }

    public function test_that_data_can_be_retrieved_for_a_specific_locale()
    {
        $this->store->targetLocale('en');
        $this->store->set('foo', 'bar');
        $this->store->set('hello', 'world');

        $this->store->targetLocale('fr');
        $this->store->set('foo', 'le bar');

        $this->assertEquals(['foo' => 'le bar'], $this->store->locale('fr')->all());
    }
}