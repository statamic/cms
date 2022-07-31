<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
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

    /** @test */
    public function gets_partials_from_views_directory()
    {
        $this->viewShouldReturnRaw('mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('mypartial'));
    }

    /** @test */
    public function gets_partials_from_partials_directory()
    {
        $this->viewShouldReturnRaw('partials.sub.mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    /** @test */
    public function gets_partials_with_underscore_prefix()
    {
        $this->viewShouldReturnRaw('sub._mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    /** @test */
    public function gets_partials_with_underscore_prefix_from_partials_directory()
    {
        $this->viewShouldReturnRaw('partials.sub._mypartial', 'the partial content');

        $this->assertEquals('the partial content', $this->partialTag('sub.mypartial'));
    }

    /** @test */
    public function partials_can_contain_front_matter()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ view:foo }}");

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial')
        );
    }

    /** @test */
    public function partials_can_pass_data_through_params()
    {
        $this->viewShouldReturnRaw('mypartial', 'the partial content with {{ foo }}');

        $this->assertEquals(
            'the partial content with bar',
            $this->partialTag('mypartial', 'foo="bar"')
        );
    }

    /** @test */
    public function partials_have_slots_when_used_as_pair()
    {
        $this->viewShouldReturnRaw('mypartial', 'before {{ slot }} after');

        $this->assertEquals(
            'before bar outside after',
            $this->tag('{{ partial:mypartial }}{{ foo }} outside{{ /partial:mypartial }}', ['foo' => 'bar'])
        );
    }

    /** @test */
    public function parameter_will_override_partial_front_matter()
    {
        $this->viewShouldReturnRaw('mypartial', "---\nfoo: bar\n---\nthe partial content with {{ foo }}");

        $this->assertEquals(
            'the partial content with baz',
            $this->partialTag('mypartial', 'foo="baz"')
        );
    }

    /** @test */
    public function parameters_can_be_accessed_within_slots()
    {
        $this->viewShouldReturnRaw('mypartial', '{{ slot }}');

        $this->assertEquals(
            'slot start bar slot end',
            $this->tag('{{ partial:mypartial foo="bar" }}slot start {{ foo }} slot end{{ /partial:mypartial }}')
        );
    }

    /** @test */
    public function parameters_can_be_accessed_within_named_slots()
    {
        $this->viewShouldReturnRaw('mypartial', '<slot>{{ slot }}</slot><named:slot>{{ slot:footer | upper }}</named:slot>');

        $template = <<<'EOT'
{{ partial:mypartial foo="bar" }}
    {{ slot:footer }}
        Footer: {{ foo }}
    {{ /slot:footer }}
    
    Slot: {{ foo }}
{{ /partial:mypartial }}
EOT;

        $this->assertEquals(
            '<slot>Slot: bar</slot><named:slot>FOOTER: BAR</named:slot>',
            $this->tag($template)
        );
    }
}
