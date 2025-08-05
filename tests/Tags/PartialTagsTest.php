<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\StaticCaching\NoCache\Session;
use Tests\FakesViews;
use Tests\TestCase;

class PartialTagsTest extends TestCase
{
    use FakesViews;

    public function setUp(): void
    {
        parent::setUp();
        $this->withFakeViews();
    }

    private function tag($tag, $context = [])
    {
        return (string) Parse::template($tag, $context);
    }

    protected function partialTag($src, $params = '')
    {
        return $this->tag("{{ partial:{$src} $params }}");
    }

    #[Test]
    public function gets_partials_from_views_directory()
    {
        $this->viewShouldReturnRaw('mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('mypartial'));
    }

    #[Test]
    public function gets_partials_from_partials_directory()
    {
        $this->viewShouldReturnRaw('partials.sub.mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    #[Test]
    public function gets_partials_with_underscore_prefix()
    {
        $this->viewShouldReturnRaw('sub._mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    #[Test]
    public function gets_partials_with_underscore_prefix_from_partials_directory()
    {
        $this->viewShouldReturnRaw('partials.sub._mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    #[Test]
    public function partials_can_contain_front_matter()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ view:foo }}");

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial')
        );
    }

    #[Test]
    public function partials_can_pass_data_through_params()
    {
        $this->viewShouldReturnRaw('mypartial', 'the partial content with {{ foo }}');

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial', 'foo="bar"')
        );
    }

    #[Test]
    public function partials_have_slots_when_used_as_pair()
    {
        $this->viewShouldReturnRaw('mypartial', 'before {{ slot }} after');

        $this->assertEquals(
            'before bar outside after',
            $this->tag('{{ partial:mypartial }}{{ foo }} outside{{ /partial:mypartial }}', ['foo' => 'bar'])
        );
    }

    #[Test]
    public function parameter_will_override_partial_front_matter()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with baz',
            $this->partialTag('mypartial', 'foo="baz"')
        );
    }

    #[Test]
    public function it_doesnt_render_partial_if_when_condition_is_false()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            '',
            $this->partialTag('mypartial', 'foo="baz" when="false"')
        );
    }

    #[Test]
    public function it_renders_partial_if_when_condition_is_true()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with baz',
            $this->partialTag('mypartial', 'foo="baz" when="true"')
        );
    }

    #[Test]
    public function it_doesnt_render_partial_if_unless_condition_is_true()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            '',
            $this->partialTag('mypartial', 'foo="baz" unless="true"')
        );
    }

    #[Test]
    public function it_renders_partial_if_unless_condition_is_false()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with baz',
            $this->partialTag('mypartial', 'foo="baz" unless="false"')
        );
    }

    #[Test]
    public function it_supports_rendering_with_nocache()
    {
        $this->viewShouldReturnRaw('test', 'This is a partial');

        $partial = $this->partialTag('test', 'nocache="true"');
        $region = app(Session::class)->regions()->first();

        $this->assertNotEquals('This is a partial', $partial);
        $this->assertEquals(
            vsprintf('<span class="nocache" data-nocache="%s">%s</span>', [
                $region->key(),
                'NOCACHE_PLACEHOLDER',
            ]),
            $partial,
        );
    }

    #[Test]
    public function it_supports_not_rendering_with_nocache()
    {
        $this->viewShouldReturnRaw('test', 'This is a partial');

        $partial = $this->partialTag('test', 'nocache="false"');
        $region = app(Session::class)->regions()->first();

        $this->assertEquals('This is a partial', $partial);
        $this->assertNull($region);
    }
}
