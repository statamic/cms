<?php

namespace Tests\View\Blade\AntlersComponents;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Support\Str;
use Tests\TestCase;

#[Group('blade-compiler')]
class ScopeTagTest extends TestCase
{
    #[Test]
    public function it_creates_scope_array()
    {
        $template = <<<'BLADE'
<s:scope:stuff>
  @php($title = 'A different title')
  {{ $stuff['title'] }}
  {{ $title }}
</s:scope:stuff>
BLADE;

        $this->assertSame(
            'The Title A different title',
            Str::squish(Blade::render($template, ['title' => 'The Title'])),
        );
    }
}
