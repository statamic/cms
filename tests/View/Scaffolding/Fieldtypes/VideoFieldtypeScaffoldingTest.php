<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class VideoFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'video',
    ];

    #[Test]
    public function it_scaffolds_video_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ if test | is_embeddable }}
    <!-- Embeddable video sources, like YouTube and Vimeo -->
    <iframe src="{{ test | embed_url }}"></iframe>
{{ else }}
    <!-- Other HTML5 video types -->
    <video src="{{ test | embed_url }}"></video>
{{ /if }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_video_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ if root:nested_group:test | is_embeddable }}
    <!-- Embeddable video sources, like YouTube and Vimeo -->
    <iframe src="{{ root:nested_group:test | embed_url }}"></iframe>
{{ else }}
    <!-- Other HTML5 video types -->
    <video src="{{ root:nested_group:test | embed_url }}"></video>
{{ /if }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_video_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@if (Statamic::modify($test)->isEmbeddable()->fetch())
    <!-- Embeddable video sources, like YouTube and Vimeo -->
    <iframe src="{{ Statamic::modify($test)->embedUrl() }}"></iframe>
@else
    <!-- Other HTML5 video types -->
    <video src="{{ Statamic::modify($test)->embedUrl() }}"></video>
@endif
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_video_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@if (Statamic::modify($root->nested_group->test)->isEmbeddable()->fetch())
    <!-- Embeddable video sources, like YouTube and Vimeo -->
    <iframe src="{{ Statamic::modify($root->nested_group->test)->embedUrl() }}"></iframe>
@else
    <!-- Other HTML5 video types -->
    <video src="{{ Statamic::modify($root->nested_group->test)->embedUrl() }}"></video>
@endif
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
