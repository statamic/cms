<?php

namespace Tests\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\MoveAsset;
use Statamic\Actions\MoveAssetFolder;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MoveAssetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $container;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->container = tap(
            (new AssetContainer)->handle('test_container')->disk('test')
        )->save();
    }

    #[Test]
    #[DataProvider('moveActionsProvider')]
    public function move_action_folder_field_lists_all_destination_folders($action)
    {
        $this->container
            ->makeAsset('source/one.txt')
            ->upload(UploadedFile::fake()->create('one.txt'));
        $this->container
            ->makeAsset('destination/two.txt')
            ->upload(UploadedFile::fake()->create('two.txt'));

        $field = (new $action)
            ->context(['container' => 'test_container'])
            ->toArray()['fields'][0];

        $folders = $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->getJson(cp_route('relationship.index').'?'.http_build_query([
                'config' => base64_encode(json_encode($field)),
                'container' => 'test_container',
            ]))
            ->assertOk()
            ->collect('data')
            ->pluck('id')
            ->all();

        $this->assertEqualsCanonicalizing(['/', 'destination', 'source'], $folders);
    }

    public static function moveActionsProvider()
    {
        return [
            'asset' => [MoveAsset::class],
            'asset folder' => [MoveAssetFolder::class],
        ];
    }
}
