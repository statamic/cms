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
    public function it_moves_asset_when_no_conflict_exists(): void
    {
        $this->createAsset('source/logo.svg', 'new');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->move('source/logo.svg', 'target')
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Storage::disk('test')->assertMissing('source/logo.svg');
        Storage::disk('test')->assertExists('target/logo.svg');
        $this->assertEquals('new', Storage::disk('test')->get('target/logo.svg'));
    }

    #[Test]
    public function it_is_a_no_op_when_moving_to_the_same_folder(): void
    {
        $this->createAsset('source/logo.svg', 'contents');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->move('source/logo.svg', 'source')
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Storage::disk('test')->assertExists('source/logo.svg');
        Storage::disk('test')->assertMissing('target/logo.svg');
        $this->assertEquals('contents', Storage::disk('test')->get('source/logo.svg'));
    }

    #[Test]
    public function it_reports_completed_moves_when_a_later_asset_conflicts(): void
    {
        $this->createAsset('source/a.svg', 'a');
        $this->createAsset('source/b.svg', 'b');
        $this->createAsset('target/b.svg', 'existing');

        $idA = 'test_container::source/a.svg';
        $idB = 'test_container::source/b.svg';
        $idAAtTarget = 'test_container::target/a.svg';

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->post(cp_route('assets.actions.run'), [
                'action' => 'move_asset',
                'context' => ['container' => 'test_container'],
                'selections' => [$idA, $idB],
                'values' => ['folder' => 'target'],
            ])
            ->assertOk()
            ->assertJson([
                'success' => false,
                'conflict' => [
                    'type' => 'asset_move',
                    'asset' => [
                        'id' => $idB,
                    ],
                ],
                'completed_moves' => [
                    $idA => $idAAtTarget,
                ],
            ]);

        Storage::disk('test')->assertMissing('source/a.svg');
        Storage::disk('test')->assertExists('target/a.svg');
        Storage::disk('test')->assertExists('source/b.svg');
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
    public function it_blocks_conflicting_move_with_explicit_cancel_strategy(): void
    {
        $this->createAsset('source/logo.svg', 'new');
        $this->createAsset('target/logo.svg', 'existing');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->move('source/logo.svg', 'target', 'cancel')
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
        $this->assertEquals('new', Storage::disk('test')->get('source/logo.svg'));
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
        $this->assertCount(1, $this->container->assets('/', true));
        $this->assertSame(['test_container::target/logo.svg'], $this->container->assets('/', true)->pluck('id')->values()->all());
    }

    #[Test]
    public function it_overwrites_a_destination_file_that_has_no_asset_record(): void
    {
        $this->createAsset('source/logo.svg', 'new');
        Storage::disk('test')->put('target/logo.svg', 'existing');

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
        $this->assertSame(['test_container::target/logo.svg'], $this->container->assets('/', true)->pluck('id')->values()->all());
    }

    #[Test]
    public function it_cannot_overwrite_without_delete_permission(): void
    {
        $this->createAsset('source/logo.svg', 'new');
        $this->createAsset('target/logo.svg', 'existing');

        $this->setTestRoles([
            'mover' => ['access cp', 'move test_container assets'],
        ]);

        $this
            ->actingAs(tap(User::make()->assignRole('mover'))->save())
            ->move('source/logo.svg', 'target', 'overwrite')
            ->assertOk()
            ->assertJson([
                'success' => false,
                'message' => 'You are not authorized to delete this asset.',
            ]);

        Storage::disk('test')->assertExists('source/logo.svg');
        Storage::disk('test')->assertExists('target/logo.svg');
        $this->assertEquals('new', Storage::disk('test')->get('source/logo.svg'));
        $this->assertEquals('existing', Storage::disk('test')->get('target/logo.svg'));
    }

    #[Test]
    public function it_can_overwrite_when_user_has_move_and_delete_permissions(): void
    {
        $this->createAsset('source/logo.svg', 'new');
        $this->createAsset('target/logo.svg', 'existing');

        $this->setTestRoles([
            'mover' => ['access cp', 'move test_container assets', 'delete test_container assets'],
        ]);

        $this
            ->actingAs(tap(User::make()->assignRole('mover'))->save())
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

    #[Test]
    public function it_does_not_add_index_suffix_to_first_conflicting_asset_in_batch_with_non_conflicting_assets_before_it(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1712000000, config('app.timezone')));

        $this->createAsset('source/a.svg', 'a-new');
        $this->createAsset('source/logo.svg', 'new');
        $this->createAsset('target/logo.svg', 'existing');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->post(cp_route('assets.actions.run'), [
                'action' => 'move_asset',
                'context' => ['container' => 'test_container', 'conflict' => 'timestamp'],
                'selections' => ['test_container::source/a.svg', 'test_container::source/logo.svg'],
                'values' => ['folder' => 'target'],
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Storage::disk('test')->assertMissing('source/a.svg');
        Storage::disk('test')->assertMissing('source/logo.svg');
        Storage::disk('test')->assertExists('target/a.svg');
        Storage::disk('test')->assertExists('target/logo.svg');
        Storage::disk('test')->assertExists('target/logo-1712000000.svg');
        $this->assertEquals('a-new', Storage::disk('test')->get('target/a.svg'));
        $this->assertEquals('existing', Storage::disk('test')->get('target/logo.svg'));
        $this->assertEquals('new', Storage::disk('test')->get('target/logo-1712000000.svg'));
    }
}
