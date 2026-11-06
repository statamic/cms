<?php

namespace Tests\View\Blade\AntlersComponents;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Tags;
use Tests\TestCase;

#[Group('blade-compiler')]
class NamespacedTagsTest extends TestCase
{
    #[Test]
    public function it_renders_namespaced_tags_with_a_method()
    {
        $this->assertSame('hi', Blade::render('<s:acme::ns_greet:hello />'));
    }

    #[Test]
    public function it_renders_namespaced_tags_without_a_method()
    {
        $this->assertSame('greetings', Blade::render('<s:acme::ns_greet />'));
    }

    #[Test]
    public function it_renders_paired_namespaced_tags()
    {
        $template = <<<'BLADE'
<s:acme::ns_items>{{ $value }}</s:acme::ns_items>
BLADE;

        $this->assertSame('ab', Blade::render($template));
    }

    #[Test]
    public function it_renders_namespaced_tag_aliases()
    {
        $this->assertSame('hi', Blade::render('<s:acme::ns_hi:hello />'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('view:clear');

        (new class extends Tags
        {
            public static $handle = 'ns_greet';

            protected static $aliases = ['ns_hi'];

            public function index()
            {
                return 'greetings';
            }

            public function hello()
            {
                return 'hi';
            }
        })::register('acme');

        (new class extends Tags
        {
            public static $handle = 'ns_items';

            public function index()
            {
                return [['value' => 'a'], ['value' => 'b']];
            }
        })::register('acme');
    }
}
