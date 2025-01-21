<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Tests\Fixtures\Addon\Tags\TestTags;
use Tests\TestCase;

class LoaderTest extends TestCase
{
    #[Test]
    public function loading_a_tag_will_run_the_init_hook()
    {
        TestTags::register();
        $test = $this;
        $tag = null;

        // This hook should add to the tag's params, which we can assert about later.
        TestTags::hook('init', function ($payload, $next) use (&$tag, $test) {
            $test->assertInstanceOf(TestTags::class, $this);
            $this->params['alfa'] = 'bravo';
            $tag = $this;

            return $next($payload);
        });

        $this->assertEquals('bar', (string) Antlers::parse('{{ test :variable="foo" }}', ['foo' => 'bar']));
        $this->assertEquals(['variable' => 'bar', 'alfa' => 'bravo'], $tag->params->all());
    }
}
