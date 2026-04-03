<?php

namespace Tests\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MoveAssetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private AssetContainer $container;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->container = tap(
            (new AssetContainer)->handle('test_container')->disk('test')
        )->save();
    }

    private function createAsset(string $path, string $contents = 'contents'): void
    {
        Storage::disk('test')->put($path, $contents);
        $this->container->makeAsset($path)->save();
    }

    private function move(string $path, string $folder, ?string $strategy = null)
    {
        $context = ['container' => 'test_container'];

        if ($strategy) {
            $context['conflict'] = $strategy;
        }

        return $this->post(cp_route('assets.actions.run'), [
            'action' => 'move_asset',
            'context' => $context,
            'selections' => ['test_container::'.$path],
            'values' => ['folder' => $folder],
        ]);
    }

    #[Test]
    public function it_blocks_conflicting_move_without_strategy(): void
    {
        $this->createAsset('source/logo.svg', 'new');
        $this->createAsset('target/logo.svg', 'existing');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->move('source/logo.svg', 'target')
            ->assertOk()
            ->assertJson([
                'success' => false,
                'conflict' => [
                    'type' => 'asset_move',
                    'destination' => 'target',
                ],
            ]);

        Storage::disk('test')->assertExists('source/logo.svg');
        Storage::disk('test')->assertExists('target/logo.svg');
        $this->assertEquals('existing', Storage::disk('test')->get('target/logo.svg'));
    }

    #[Test]
    public function it_can_overwrite_conflicting_move_when_strategy_is_overwrite(): void
    {
        $this->createAsset('source/logo.svg', 'new');
        $this->createAsset('target/logo.svg', 'existing');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->move('source/logo.svg', 'target', 'overwrite')
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Storage::disk('test')->assertMissing('source/logo.svg');
        Storage::disk('test')->assertExists('target/logo.svg');
        $this->assertEquals('new', Storage::disk('test')->get('target/logo.svg'));
    }

    #[Test]
    public function it_can_keep_both_with_timestamp_strategy(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1712000000, config('app.timezone')));

        $this->createAsset('source/logo.svg', 'new');
        $this->createAsset('target/logo.svg', 'existing');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->move('source/logo.svg', 'target', 'timestamp')
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Storage::disk('test')->assertMissing('source/logo.svg');
        Storage::disk('test')->assertExists('target/logo.svg');
        Storage::disk('test')->assertExists('target/logo-1712000000.svg');
        $this->assertEquals('existing', Storage::disk('test')->get('target/logo.svg'));
        $this->assertEquals('new', Storage::disk('test')->get('target/logo-1712000000.svg'));
    }
}
