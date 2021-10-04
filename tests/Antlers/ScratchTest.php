<?php

namespace Tests\Antlers;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Antlers;
use Statamic\Facades\Collection;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ScratchTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function nav_tag_variables_should_not_leak_outside_its_tag_pair()
    {
        $collection = Collection::make('test');
        $structure = (new CollectionStructure)->maxDepth(3)->expectsRoot(false);
        $collection->structure($structure)->save();
        EntryFactory::collection('test')->id('one')->slug('one')->data(['title' => 'One'])->create();
        EntryFactory::collection('test')->id('two')->slug('two')->data(['title' => 'Two'])->create();
        $collection->structure()->in('en')->tree([
            ['entry' => 'one'],
            ['entry' => 'two'],
        ])->save();

        $template = '{{ title }} {{ nav:collection:test }}{{ title }} {{ /nav:collection:test }} {{ title }}';
        $expected = 'Outside One Two  Outside';

        $parsed = (string) Antlers::parse($template, ['title' => 'Outside']);

        $this->assertEquals($expected, $parsed);
    }
}
