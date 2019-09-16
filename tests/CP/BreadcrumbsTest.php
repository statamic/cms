<?php

namespace Tests\CP;

use Tests\TestCase;
use Statamic\CP\Breadcrumbs;
use Illuminate\Contracts\Translation\Translator;

class BreadcrumbsTest extends TestCase
{
    /** @test */
    function it_creates_breadcrumbs()
    {
        $bc = new Breadcrumbs($array = [
            ['text' => 'First', 'url' => '/first'],
            ['text' => 'Second', 'url' => '/second'],
        ]);

        $this->assertSame($array, $bc->toArray());
        $this->assertSame(json_encode($array), $bc->toJson());
        $this->assertEquals('Second ‹ First', $bc->title());
    }

    /** @test */
    function it_pushes_a_crumb_into_the_title()
    {
        app()->instance('translator', $this->mock(Translator::class)
            ->shouldReceive('get')->with('The title', [], null)->once()
            ->andReturn('The translated title')
            ->getMock()
        );

        $bc = new Breadcrumbs($array = [
            ['text' => 'First', 'url' => '/first'],
            ['text' => 'Second', 'url' => '/second'],
        ]);

        $title = $bc->title('The title');

        $this->assertSame($array, $bc->toArray());
        $this->assertSame(json_encode($array), $bc->toJson());
        $this->assertEquals('The translated title ‹ Second ‹ First', $title);
    }
}
