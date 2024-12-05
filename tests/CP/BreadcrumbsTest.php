<?php

namespace Tests\CP;

use Illuminate\Contracts\Translation\Translator;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Breadcrumbs;
use Tests\TestCase;

class BreadcrumbsTest extends TestCase
{
    #[Test]
    public function it_creates_breadcrumbs()
    {
        $bc = new Breadcrumbs($array = [
            ['text' => 'First', 'url' => '/first'],
            ['text' => 'Second', 'url' => '/second'],
        ]);

        $this->assertSame($array, $bc->toArray());
        $this->assertSame(json_encode($array), $bc->toJson());
        $this->assertEquals('Second ‹ First', $bc->title());
    }

    #[Test]
    public function it_is_arrayable()
    {
        $bc = new Breadcrumbs($array = [
            ['text' => 'First', 'url' => '/first'],
            ['text' => 'Second', 'url' => '/second'],
        ]);

        $collection = collect(['breadcrumbs' => $bc]);

        $this->assertSame(['breadcrumbs' => $array], $collection->toArray());
    }

    #[Test]
    public function it_pushes_a_crumb_into_the_title()
    {
        $translator = Mockery::mock(app(Translator::class))
            ->makePartial()
            ->shouldReceive('get')->with('The title', [], null)->once()
            ->andReturn('The translated title')
            ->getMock();

        app()->instance('translator', $translator);

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
