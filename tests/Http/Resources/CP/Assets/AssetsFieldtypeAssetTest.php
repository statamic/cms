<?php

namespace Tests\Http\Resources\CP\Assets;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Assets\AssetsFieldtypeAsset;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsFieldtypeAssetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('img/photo.jpg', '');

        $this->container = AssetContainer::make('test')->disk('test')->save();
    }

    #[Test]
    public function it_returns_expected_data()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $asset = AssetContainer::find('test')->asset('img/photo.jpg');

        $resource = (new AssetsFieldtypeAsset($asset))->resolve()['data'];

        $this->assertEquals('test::img/photo.jpg', $resource['id']);
        $this->assertEquals('photo.jpg', $resource['basename']);
        $this->assertEquals('jpg', $resource['extension']);
        $this->assertEquals('/assets/img/photo.jpg', $resource['url']);
        $this->assertArrayHasKey('downloadUrl', $resource);
        $this->assertArrayHasKey('size', $resource);
        $this->assertTrue($resource['isImage']);
        $this->assertFalse($resource['isSvg']);
        $this->assertTrue($resource['isMedia']);
        $this->assertTrue($resource['isEditable']);
        $this->assertTrue($resource['isViewable']);
        $this->assertArrayHasKey('thumbnail', $resource);
        $this->assertArrayHasKey('values', $resource);
        $this->assertArrayHasKey('meta', $resource);

        $this->assertArrayNotHasKey('actions', $resource);
        $this->assertArrayNotHasKey('actionUrl', $resource);
        $this->assertArrayNotHasKey('blueprint', $resource);
    }

    #[Test]
    public function it_reflects_permissions()
    {
        $this->setTestRoles(['test' => ['view test assets']]);

        $user = tap(User::make()->assignRole('test'))->save();
        $this->actingAs($user);

        $asset = AssetContainer::find('test')->asset('img/photo.jpg');

        $resource = (new AssetsFieldtypeAsset($asset))->resolve()['data'];

        $this->assertFalse($resource['isEditable']);
        $this->assertTrue($resource['isViewable']);
    }
}
