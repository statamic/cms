<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
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
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.jpg', UploadedFile::fake()->image('a.jpg')->getContent());
        tap(AssetContainer::make('test')->disk('test'))->save();
        Asset::find('test::a.jpg')->data(['alt' => 'Alpha'])->save();

        $template = <<<'EOT'
{{ img = { asset url="/assets/a.jpg" } }}
{{ img }}|{{ img.url }}|{{ img.alt }}|{{ img:alt }}
EOT;

        $this->assertSame('/assets/a.jpg|/assets/a.jpg|Alpha|Alpha', trim($this->renderString($template, [], true)));
    }
}
