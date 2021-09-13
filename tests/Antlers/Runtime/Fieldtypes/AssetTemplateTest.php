<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Fields\Value;
use Tests\Antlers\Fixtures\Addon\Tags\VarTest;
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

        [$result, $runtimeData] = $this->evaluateBoth('test = test_asset; test_2 = convert.toString(test_asset)', [
            'test_asset' => $value,
        ]);

        $this->assertSame($asset, $runtimeData['test']);
        $this->assertSame('test_container::path/to/asset.jpg', $runtimeData['test_2']);
    }
}
