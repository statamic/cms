<?php

namespace Tests\View\Blade\AntlersComponents;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Tags;
use Tests\TestCase;

#[Group('blade-compiler')]
class SelfClosingTagsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('view:clear');
    }

    #[Test]
    public function it_correctly_sets_is_pair()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                if ($this->isPair) {
                    return 'Definitely a tag pair.';
                }

                return 'I am not a tag pair!';
            }
        })::register();

        $this->assertSame(
            'Definitely a tag pair.',
            Blade::render('<s:my_tag></s:my_tag>')
        );

        $this->assertSame(
            'I am not a tag pair!',
            Blade::render('<s:my_tag />')
        );

        // Please use self-closing tags, though. 🙏
        $this->assertSame(
            'I am not a tag pair!Definitely a tag pair.',
            Blade::render('<s:my_tag><s:my_tag></s:my_tag>')
        );
    }
}
