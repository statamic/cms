<?php

namespace Tests\Antlers;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Antlers;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ScratchTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function tag_variables_should_not_leak_outside_its_tag_pair()
    {
        EntryFactory::collection('test')->id('one')->slug('one')->data(['title' => 'One'])->create();
        EntryFactory::collection('test')->id('two')->slug('two')->data(['title' => 'Two'])->create();

        // note: not specific to the collection tag
        $template = '{{ title }} {{ collection:test }}{{ title }} {{ /collection:test }} {{ title }}';
        $expected = 'Outside One Two  Outside';

        $parsed = (string) Antlers::parse($template, ['title' => 'Outside']);

        $this->assertEquals($expected, $parsed);
    }
}
