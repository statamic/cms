<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;

class TagsTest extends ParserTestCase
{
    public function test_nested_double_braces_can_be_used_to_supply_parameter_values()
    {
        (new class extends Tags
        {
            public static $handle = 'test_receives_arguments';

            public function index()
            {
                return 'Test: '.$this->params->get('test');
            }
        })::register();

        $result = $this->renderString('{{# comment {{ test }} {{ value }} #}}{{ test_receives_arguments test="{{ value }}" }}{{# value #}}{{ value }}{{# value #}} - {{ value | upper }}', [
            'value' => 'test value',
        ], true);

        $this->assertSame('Test: test valuetest value - TEST VALUE', $result);

        $result = $this->renderString('{{# comment {{ test }} {{ value }} #}}{{ test_receives_arguments test="{{ value }}" }}{{# value #}}{{ value }}{{# value #}} - ', [
            'value' => 'test value',
        ], true);

        $this->assertSame('Test: test valuetest value - ', $result);
    }

    public function test_collections_returned_from_tags()
    {
        (new class extends Tags
        {
            public static $handle = 'test_tag';

            public function index()
            {
                return collect(['a' => 'b']);
            }
        })::register();

        $this->assertSame('b', $this->renderString('{{ test_tag }}{{ a }}{{ /test_tag }}', [], true));
    }

    /**
     * @see https://github.com/statamic/cms/issues/11257
     */
    public function test_objects_returned_from_tags_keep_their_data_when_assigned_to_a_variable()
    {
        $this->createAsset();

        $template = <<<'EOT'
{{ img = { asset url="/assets/a.jpg" } }}
{{ img }}|{{ img.url }}|{{ img.alt }}|{{ img:alt }}
EOT;

        $this->assertSame('/assets/a.jpg|/assets/a.jpg|Alpha|Alpha', trim($this->renderString($template, [], true)));
    }

    public function test_objects_returned_from_tags_can_be_looped_over_after_being_assigned_to_a_variable()
    {
        $this->createAsset();

        $template = <<<'EOT'
{{ img = { asset url="/assets/a.jpg" } }}
{{ img }}[{{ alt }}]{{ /img }}
EOT;

        $this->assertSame('[Alpha]', trim($this->renderString($template, [], true)));
    }

    #[DataProvider('dynamicKeyProvider')]
    public function test_objects_returned_from_tags_are_strings_when_used_as_dynamic_keys($template)
    {
        $this->createAsset();

        $this->assertSame('matched', $this->renderString($template, ['items' => ['/assets/a.jpg' => 'matched']], true));
    }

    public static function dynamicKeyProvider()
    {
        return [
            'dot syntax' => ['{{ items.{asset url="/assets/a.jpg"} }}'],
            'bracket syntax' => ['{{ items[{asset url="/assets/a.jpg"}] }}'],
        ];
    }

    #[DataProvider('comparisonProvider')]
    public function test_objects_returned_from_tags_are_strings_when_compared_in_conditions($template, $expected)
    {
        $this->createAsset();

        $this->assertSame($expected, $this->renderString($template, [], true));
    }

    public static function comparisonProvider()
    {
        return [
            'identical' => ['{{ if {asset url="/assets/a.jpg"} === "/assets/a.jpg" }}yes{{ else }}no{{ /if }}', 'yes'],
            'not identical' => ['{{ if {asset url="/assets/a.jpg"} !== "/assets/a.jpg" }}yes{{ else }}no{{ /if }}', 'no'],
            'ternary' => ['{{ {asset url="/assets/a.jpg"} === "/assets/a.jpg" ? "yes" : "no" }}', 'yes'],
        ];
    }

    public function test_values_returned_from_tags_are_unwrapped_when_assigned_to_a_variable()
    {
        (new class extends Tags
        {
            public static $handle = 'test_value';

            public function index()
            {
                return new Value('wrapped');
            }
        })::register();

        (new class extends Tags
        {
            public static $handle = 'test_values';

            public function index()
            {
                return new Values(['a' => 'b']);
            }
        })::register();

        $this->assertSame('wrapped', $this->renderString('{{ value = {test_value} }}{{ value }}', [], true));
        $this->assertSame('b', $this->renderString('{{ values = {test_values} }}{{ values:a }}', [], true));
    }

    public function test_missing_assets_assign_nothing_to_a_variable()
    {
        $this->createAsset();

        $this->assertSame('[]', $this->renderString('{{ img = { asset url="/assets/missing.jpg" } }}[{{ img }}]', [], true));
    }

    private function createAsset()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.jpg', UploadedFile::fake()->image('a.jpg')->getContent());
        tap(AssetContainer::make('test')->disk('test'))->save();
        Asset::find('test::a.jpg')->data(['alt' => 'Alpha'])->save();
    }
}
