<?php

namespace Tests\View\Blade\AntlersComponents;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Tags;
use Tests\TestCase;

#[Group('blade-compiler')]
class TagContentsTest extends TestCase
{
    #[Test]
    public function it_respects_tags_swapping_content()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                if ($this->params->get('swap', false)) {
                    $this->content = 'No Swiping!';
                }

                return $this->parse();
            }
        })::register();

        $this->assertSame(
            'Original Stuff.',
            Blade::render('<s:my_tag>Original Stuff.</s:my_tag>')
        );

        $this->assertSame(
            'No Swiping!',
            Blade::render('<s:my_tag :swap="true">Original Stuff.</s:my_tag>')
        );
    }
}
