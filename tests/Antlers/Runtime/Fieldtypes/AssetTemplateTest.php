<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\Fixtures\Addon\Tags\VarTestTags as VarTest;
use Tests\Antlers\ParserTestCase;

class AssetTemplateTest extends ParserTestCase
{
    protected $container = null;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
        ]]);

        $this->container = (new AssetContainer)
            ->handle('test_container')
            ->disk('test');

        Storage::fake('test');
        Storage::fake('dimensions-cache');
    }

    public function test_asset_returns_implicit_string_value()
    {
        $asset = new Asset();
        $asset->container($this->container);
        $asset->path('path/to/asset.jpg');

        $value = new Value($asset, 'test_asset');

        $this->assertSame('test_container::path/to/asset.jpg', $this->renderString(
            '{{ test_asset }}', [
                'test_asset' => $value,
            ]
        ));

        $this->assertSame('path/to/asset.jpg', $this->renderString(
            '{{ test_asset:path }}', [
                'test_asset' => $value,
            ]
        ));
    }

    public function test_passing_asset_in_parameter_actually_passes_asset()
    {
        VarTest::register();

        $asset = new Asset();
        $asset->container($this->container);
        $asset->path('path/to/asset.jpg');

        $value = new Value($asset, 'test_asset');

        $this->renderString(
            '{{ var_test :variable="test_asset" }}', [
                'test_asset' => $value,
            ], true
        );

        $this->assertSame($asset, VarTest::$var);
    }

    public function test_asset_assignment()
    {
        $asset = new Asset();
        $asset->container($this->container);
        $asset->path('path/to/asset.jpg');

        $value = new Value($asset, 'test_asset');

        [$result, $runtimeData] = $this->evaluateBoth('test = test_asset; test_2 = (test_asset|to_string())', [
            'test_asset' => $value,
        ]);

        $this->assertSame($asset, $runtimeData['test']);
        $this->assertSame('test_container::path/to/asset.jpg', $runtimeData['test_2']);
    }

    public function test_parameter_values_are_not_lost_when_passed_into_tags()
    {
        $asset = new Asset();
        $asset->container($this->container);
        $asset->path('path/to/asset.jpg');

        $value = new Value($asset, 'test_asset');

        $data = [
            'image' => $value,
        ];

        // These partials are located in /tests/__fixtures__/views/
        $template = <<<'EOT'
Root: {{ image | class_name }}
{{ partial:example :image="image" }}
EOT;

        // Inside the nested partial three, image is being overwritten to an integer to ensure
        // that overrides like that work, and it is set back to image for nested_four.

        $expected = <<<'EOT'
Root: Statamic\Assets\Asset
Example: Statamic\Assets\Asset
Nested One: Statamic\Assets\Asset
Nested Two: Statamic\Assets\Asset
Nested Three Asset: Statamic\Assets\Asset
Nested Three Image Var: 123 - integer
Nested Four: Statamic\Assets\Asset
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, $data, true));
    }
}
