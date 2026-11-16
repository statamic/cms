<?php

namespace Tests\Actions;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\ReplaceAsset;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ReplaceAssetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');
        Storage::fake('other');

        tap((new AssetContainer)->handle('test_container')->disk('test'))->save();
        tap((new AssetContainer)->handle('other_container')->disk('other'))->save();
        tap(Collection::make('pages'))->save();
    }

    public function tearDown(): void
    {
        Blueprint::in('collections/pages')->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_lets_you_browse_other_folders_when_no_field_restricts_them()
    {
        $this->blueprintWithFields([
            ['handle' => 'image', 'field' => ['type' => 'assets', 'container' => 'test_container']],
        ]);

        $config = $this->newAssetFieldConfig();

        $this->assertFalse($config['restrict']);
        $this->assertEquals('path/to', $config['folder']);
    }

    #[Test]
    public function it_restricts_to_the_folder_when_a_field_restricts_folders()
    {
        $this->blueprintWithFields([
            ['handle' => 'image', 'field' => ['type' => 'assets', 'container' => 'test_container', 'restrict' => true]],
        ]);

        $this->assertTrue($this->newAssetFieldConfig()['restrict']);
    }

    #[Test]
    public function it_restricts_to_the_folder_when_a_field_uses_dynamic_folders()
    {
        $this->blueprintWithFields([
            ['handle' => 'image', 'field' => ['type' => 'assets', 'container' => 'test_container', 'dynamic' => 'id']],
        ]);

        $this->assertTrue($this->newAssetFieldConfig()['restrict']);
    }

    #[Test]
    public function it_restricts_to_the_folder_when_a_nested_field_restricts_folders()
    {
        $this->blueprintWithFields([
            ['handle' => 'blocks', 'field' => ['type' => 'replicator', 'sets' => [
                'main' => ['sets' => [
                    'gallery' => ['fields' => [
                        ['handle' => 'images', 'field' => ['type' => 'grid', 'fields' => [
                            ['handle' => 'image', 'field' => ['type' => 'assets', 'container' => 'test_container', 'restrict' => true]],
                        ]]],
                    ]],
                ]],
            ]]],
        ]);

        $this->assertTrue($this->newAssetFieldConfig()['restrict']);
    }

    #[Test]
    public function it_ignores_restricted_fields_on_other_containers()
    {
        $this->blueprintWithFields([
            ['handle' => 'image', 'field' => ['type' => 'assets', 'container' => 'other_container', 'restrict' => true]],
        ]);

        $this->assertFalse($this->newAssetFieldConfig()['restrict']);
    }

    private function blueprintWithFields(array $fields): void
    {
        Blueprint::make('page')->setNamespace('collections.pages')->setContents([
            'tabs' => ['main' => ['sections' => [['fields' => $fields]]]],
        ])->save();
    }

    private function newAssetFieldConfig(): array
    {
        return (new ReplaceAsset)
            ->context(['container' => 'test_container', 'folder' => 'path/to'])
            ->fields()
            ->get('new_asset')
            ->config();
    }
}
