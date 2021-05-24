<?php

namespace Tests\Stache;

use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\Store;
use Tests\TestCase;

class StoreTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = new Stache;

        $this->store = new TestStore($stache);
    }

    /** @test */
    public function it_forces_a_trailing_slash_when_setting_the_directory()
    {
        $this->assertNull($this->store->directory());

        $return = $this->store->directory('/path/to/directory');

        $this->assertEquals($this->store, $return);
        $this->assertEquals('/path/to/directory/', $this->store->directory());

        // Check the value of the property to make sure the property was set with
        // the slash, and that ->directory() isn't just appending it.
        $property = (new \ReflectionClass($this->store))->getProperty('directory');
        $property->setAccessible(true);
        $this->assertEquals('/path/to/directory/', $property->getValue($this->store));
    }

    /** @test */
    public function it_gets_indexes()
    {
        config(['statamic.stache.indexes' => [
            'bravo',
            'kilo' => 'CustomKiloIndex',
            'lima' => 'CustomLimaIndex',
            'romeo',
        ]]);

        config(['statamic.stache.stores.test.indexes' => [
            'kilo' => 'StoreCustomKiloIndex',
            'yankee',
            'golf',
        ]]);

        $store = new class extends Store
        {
            protected $valueIndex = 'TestValueIndex';
            protected $storeIndexes = [
                'alfa',
                'bravo' => 'CustomBravoIndex',
                'tango' => 'CustomTangoIndex',
                'victor',
            ];

            public function key()
            {
                return 'test';
            }

            public function getItem($key)
            {
                //
            }
        };

        Cache::forever('stache::indexes::test::_indexes', ['alfa', 'bravo', 'zulu']);

        $this->assertSame([
            'id' => 'TestValueIndex', // default
            'path' => 'TestValueIndex', // default
            'alfa' => 'TestValueIndex', // usage
            'bravo' => 'CustomBravoIndex', // usage, overridden by store index
            'zulu' => 'TestValueIndex', // usage
            'kilo' => 'StoreCustomKiloIndex', // indexes config, overridden class from store indexes config
            'lima' => 'CustomLimaIndex', // indexes config
            'romeo' => 'TestValueIndex', // indexes config
            'yankee' => 'TestValueIndex', // store indexes config
            'golf' => 'TestValueIndex', // store indexes config
            'tango' => 'CustomTangoIndex', // store
            'victor' => 'TestValueIndex', // store
        ], $store->indexes()->all());

        $this->assertSame([
            'id' => 'TestValueIndex', // default
            'path' => 'TestValueIndex', // default
            'bravo' => 'CustomBravoIndex', // indexes config, overridden by store index
            'kilo' => 'StoreCustomKiloIndex', // indexes config, overridden class from store indexes config
            'lima' => 'CustomLimaIndex', // indexes config
            'romeo' => 'TestValueIndex', // indexes config
            'yankee' => 'TestValueIndex', // store indexes config
            'golf' => 'TestValueIndex', // store indexes config
            'alfa' => 'TestValueIndex', // store
            'tango' => 'CustomTangoIndex', // store
            'victor' => 'TestValueIndex', // store
        ], $store->indexes(false)->all());
    }
}

class TestStore extends Store
{
    public function getItem($key)
    {
    }
}
