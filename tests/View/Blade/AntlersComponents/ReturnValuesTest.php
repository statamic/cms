<?php

namespace Tests\View\Blade\AntlersComponents;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Tags;
use Tests\TestCase;

#[Group('blade-compiler')]
class ReturnValuesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('view:clear');
    }

    #[Test]
    public function it_renders_arrays()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                return ['a', 'b', 'c'];
            }
        })::register();

        $template = <<<'BLADE'
<s:my_tag>
  {{ $value }}
</s:my_tag>
BLADE;

        $this->assertSame(
            'a b c',
            Str::squish(Blade::render($template))
        );
    }

    #[Test]
    public function it_renders_arrays_of_arrays()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                return [
                    ['name' => 'Alice'],
                    ['name' => 'Bob'],
                    ['name' => 'Charlie'],
                ];
            }
        })::register();

        $template = <<<'BLADE'
<s:my_tag>
  {{ $name }}
</s:my_tag>
BLADE;

        $this->assertSame(
            'Alice Bob Charlie',
            Str::squish(Blade::render($template))
        );
    }

    #[Test]
    public function it_renders_collections()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                return collect(['a', 'b', 'c']);
            }
        })::register();

        $template = <<<'BLADE'
<s:my_tag>
  {{ $value }}
</s:my_tag>
BLADE;

        $this->assertSame(
            'a b c',
            Str::squish(Blade::render($template))
        );
    }

    #[Test]
    public function it_renders_collections_of_arrays()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                return collect([
                    ['name' => 'Alice'],
                    ['name' => 'Bob'],
                    ['name' => 'Charlie'],
                ]);
            }
        })::register();

        $template = <<<'BLADE'
<s:my_tag>
  {{ $name }}
</s:my_tag>
BLADE;

        $this->assertSame(
            'Alice Bob Charlie',
            Str::squish(Blade::render($template))
        );
    }
}
