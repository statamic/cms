<?php

namespace Tests\Tags;

use Statamic\Facades\Antlers;
use Tests\Fixtures\Addon\Tags\TestTags;
use Tests\TestCase;

class LoaderTest extends TestCase
{
    /** @test */
    public function loading_a_tag_will_run_the_init_hook()
    {
        TestTags::register();
        $tag = null;

        // This hook should add to the tag's params, which we can assert about later.
        TestTags::addHook('init', function ($t) use (&$tag) {
            $this->assertInstanceOf(TestTags::class, $t);
            $t->params['alfa'] = 'bravo';
            $tag = $t;
        });

        $this->assertEquals('bar', (string) Antlers::parse('{{ test :variable="foo" }}', ['foo' => 'bar']));
        $this->assertEquals(['variable' => 'bar', 'alfa' => 'bravo'], $tag->params->all());
    }
}
