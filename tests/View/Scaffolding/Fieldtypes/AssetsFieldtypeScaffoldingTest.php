<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class AssetsFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    use PreventSavingStacheItemsToDisk;

    protected array $field = [
        'type' => 'assets',
        'container' => 'assets',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('test', ['url' => '/assets']);

        $asset = Blueprint::makeFromFields(['alt' => ['type' => 'text']]);
        AssetContainer::make('assets')->disk('test')->save();
        BlueprintRepository::shouldReceive('find')->with('assets/assets')->andReturn($asset);
    }

    #[Test]
    public function it_scaffolds_assets_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    {{ alt /}}
    {{ id /}}
    {{ title /}}
    {{ path /}}
    {{ filename /}}
    {{ basename /}}
    {{ extension /}}
    {{ is_asset /}}
    {{ is_audio /}}
    {{ is_previewable /}}
    {{ is_image /}}
    {{ is_svg /}}
    {{ is_video /}}
    {{ blueprint /}}
    {{ edit_url /}}
    {{ container /}}
    {{ folder /}}
    {{ url /}}
    {{ permalink /}}
    {{ api_url /}}

    {{# Available, if the asset exists: #}}
    {{ size /}}
    {{ size_bytes /}}
    {{ size_kilobytes /}}
    {{ size_megabytes /}}
    {{ size_gigabytes /}}
    {{ size_b /}}
    {{ size_kb /}}
    {{ size_mb /}}
    {{ size_gb /}}
    {{ last_modified /}}
    {{ last_modified_timestamp /}}
    {{ last_modified_instance /}}
    {{ focus /}}
    {{ has_focus /}}
    {{ focus_css /}}
    {{ height /}}
    {{ width /}}
    {{ orientation /}}
    {{ ratio /}}
    {{ mime_type /}}
    {{ duration /}}
    {{ duration_seconds /}}
    {{ duration_minutes /}}
    {{ duration_sec /}}
    {{ duration_min /}}
    {{ playtime /}}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_assets_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ alt /}}
    {{ id /}}
    {{ title /}}
    {{ path /}}
    {{ filename /}}
    {{ basename /}}
    {{ extension /}}
    {{ is_asset /}}
    {{ is_audio /}}
    {{ is_previewable /}}
    {{ is_image /}}
    {{ is_svg /}}
    {{ is_video /}}
    {{ blueprint /}}
    {{ edit_url /}}
    {{ container /}}
    {{ folder /}}
    {{ url /}}
    {{ permalink /}}
    {{ api_url /}}

    {{# Available, if the asset exists: #}}
    {{ size /}}
    {{ size_bytes /}}
    {{ size_kilobytes /}}
    {{ size_megabytes /}}
    {{ size_gigabytes /}}
    {{ size_b /}}
    {{ size_kb /}}
    {{ size_mb /}}
    {{ size_gb /}}
    {{ last_modified /}}
    {{ last_modified_timestamp /}}
    {{ last_modified_instance /}}
    {{ focus /}}
    {{ has_focus /}}
    {{ focus_css /}}
    {{ height /}}
    {{ width /}}
    {{ orientation /}}
    {{ ratio /}}
    {{ mime_type /}}
    {{ duration /}}
    {{ duration_seconds /}}
    {{ duration_minutes /}}
    {{ duration_sec /}}
    {{ duration_min /}}
    {{ playtime /}}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_assets_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $asset)
    {{ $asset->id }}
    {{ $asset->title }}
    {{ $asset->path }}
    {{ $asset->filename }}
    {{ $asset->basename }}
    {{ $asset->extension }}
    {{ $asset->is_asset }}
    {{ $asset->is_audio }}
    {{ $asset->is_previewable }}
    {{ $asset->is_image }}
    {{ $asset->is_svg }}
    {{ $asset->is_video }}
    {{ $asset->blueprint }}
    {{ $asset->edit_url }}
    {{ $asset->container }}
    {{ $asset->folder }}
    {{ $asset->url }}
    {{ $asset->permalink }}
    {{ $asset->api_url }}
    {{-- Available, if the asset exists: --}}
    {{ $asset->size }}
    {{ $asset->size_bytes }}
    {{ $asset->size_kilobytes }}
    {{ $asset->size_megabytes }}
    {{ $asset->size_gigabytes }}
    {{ $asset->size_b }}
    {{ $asset->size_kb }}
    {{ $asset->size_mb }}
    {{ $asset->size_gb }}
    {{ $asset->last_modified }}
    {{ $asset->last_modified_timestamp }}
    {{ $asset->last_modified_instance }}
    {{ $asset->focus }}
    {{ $asset->has_focus }}
    {{ $asset->focus_css }}
    {{ $asset->height }}
    {{ $asset->width }}
    {{ $asset->orientation }}
    {{ $asset->ratio }}
    {{ $asset->mime_type }}
    {{ $asset->duration }}
    {{ $asset->duration_seconds }}
    {{ $asset->duration_minutes }}
    {{ $asset->duration_sec }}
    {{ $asset->duration_min }}
    {{ $asset->playtime }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_assets_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $asset)
    {{ $asset->id }}
    {{ $asset->title }}
    {{ $asset->path }}
    {{ $asset->filename }}
    {{ $asset->basename }}
    {{ $asset->extension }}
    {{ $asset->is_asset }}
    {{ $asset->is_audio }}
    {{ $asset->is_previewable }}
    {{ $asset->is_image }}
    {{ $asset->is_svg }}
    {{ $asset->is_video }}
    {{ $asset->blueprint }}
    {{ $asset->edit_url }}
    {{ $asset->container }}
    {{ $asset->folder }}
    {{ $asset->url }}
    {{ $asset->permalink }}
    {{ $asset->api_url }}
    {{-- Available, if the asset exists: --}}
    {{ $asset->size }}
    {{ $asset->size_bytes }}
    {{ $asset->size_kilobytes }}
    {{ $asset->size_megabytes }}
    {{ $asset->size_gigabytes }}
    {{ $asset->size_b }}
    {{ $asset->size_kb }}
    {{ $asset->size_mb }}
    {{ $asset->size_gb }}
    {{ $asset->last_modified }}
    {{ $asset->last_modified_timestamp }}
    {{ $asset->last_modified_instance }}
    {{ $asset->focus }}
    {{ $asset->has_focus }}
    {{ $asset->focus_css }}
    {{ $asset->height }}
    {{ $asset->width }}
    {{ $asset->orientation }}
    {{ $asset->ratio }}
    {{ $asset->mime_type }}
    {{ $asset->duration }}
    {{ $asset->duration_seconds }}
    {{ $asset->duration_minutes }}
    {{ $asset->duration_sec }}
    {{ $asset->duration_min }}
    {{ $asset->playtime }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_assets_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->field([
            'max_files' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ $test->id }}
{{ $test->title }}
{{ $test->path }}
{{ $test->filename }}
{{ $test->basename }}
{{ $test->extension }}
{{ $test->is_asset }}
{{ $test->is_audio }}
{{ $test->is_previewable }}
{{ $test->is_image }}
{{ $test->is_svg }}
{{ $test->is_video }}
{{ $test->blueprint }}
{{ $test->edit_url }}
{{ $test->container }}
{{ $test->folder }}
{{ $test->url }}
{{ $test->permalink }}
{{ $test->api_url }}
{{-- Available, if the asset exists: --}}
{{ $test->size }}
{{ $test->size_bytes }}
{{ $test->size_kilobytes }}
{{ $test->size_megabytes }}
{{ $test->size_gigabytes }}
{{ $test->size_b }}
{{ $test->size_kb }}
{{ $test->size_mb }}
{{ $test->size_gb }}
{{ $test->last_modified }}
{{ $test->last_modified_timestamp }}
{{ $test->last_modified_instance }}
{{ $test->focus }}
{{ $test->has_focus }}
{{ $test->focus_css }}
{{ $test->height }}
{{ $test->width }}
{{ $test->orientation }}
{{ $test->ratio }}
{{ $test->mime_type }}
{{ $test->duration }}
{{ $test->duration_seconds }}
{{ $test->duration_minutes }}
{{ $test->duration_sec }}
{{ $test->duration_min }}
{{ $test->playtime }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_assets_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField([
            'max_files' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ $root->nested_group->test->id }}
{{ $root->nested_group->test->title }}
{{ $root->nested_group->test->path }}
{{ $root->nested_group->test->filename }}
{{ $root->nested_group->test->basename }}
{{ $root->nested_group->test->extension }}
{{ $root->nested_group->test->is_asset }}
{{ $root->nested_group->test->is_audio }}
{{ $root->nested_group->test->is_previewable }}
{{ $root->nested_group->test->is_image }}
{{ $root->nested_group->test->is_svg }}
{{ $root->nested_group->test->is_video }}
{{ $root->nested_group->test->blueprint }}
{{ $root->nested_group->test->edit_url }}
{{ $root->nested_group->test->container }}
{{ $root->nested_group->test->folder }}
{{ $root->nested_group->test->url }}
{{ $root->nested_group->test->permalink }}
{{ $root->nested_group->test->api_url }}
{{-- Available, if the asset exists: --}}
{{ $root->nested_group->test->size }}
{{ $root->nested_group->test->size_bytes }}
{{ $root->nested_group->test->size_kilobytes }}
{{ $root->nested_group->test->size_megabytes }}
{{ $root->nested_group->test->size_gigabytes }}
{{ $root->nested_group->test->size_b }}
{{ $root->nested_group->test->size_kb }}
{{ $root->nested_group->test->size_mb }}
{{ $root->nested_group->test->size_gb }}
{{ $root->nested_group->test->last_modified }}
{{ $root->nested_group->test->last_modified_timestamp }}
{{ $root->nested_group->test->last_modified_instance }}
{{ $root->nested_group->test->focus }}
{{ $root->nested_group->test->has_focus }}
{{ $root->nested_group->test->focus_css }}
{{ $root->nested_group->test->height }}
{{ $root->nested_group->test->width }}
{{ $root->nested_group->test->orientation }}
{{ $root->nested_group->test->ratio }}
{{ $root->nested_group->test->mime_type }}
{{ $root->nested_group->test->duration }}
{{ $root->nested_group->test->duration_seconds }}
{{ $root->nested_group->test->duration_minutes }}
{{ $root->nested_group->test->duration_sec }}
{{ $root->nested_group->test->duration_min }}
{{ $root->nested_group->test->playtime }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result
        );
    }
}
