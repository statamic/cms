<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\YAML;
use Statamic\Jobs\GeneratePresetImageManipulation;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsGeneratePresetsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');
        Bus::fake();

        config(['statamic.assets.image_manipulation.presets' => [
            'preset_one' => ['w' => 100],
            'preset_two' => ['w' => 200],
        ]]);
    }

    private function makeContainer(string $handle = 'test', array $warmPresets = ['preset_one', 'preset_two'])
    {
        $container = AssetContainer::make($handle)->disk('test');
        $container->warmPresets($warmPresets);
        $container->save();

        return $container;
    }

    private function putLandscapeImage(string $path = 'image.jpg')
    {
        Storage::disk('test')->put($path, '');
        Storage::disk('test')->put(
            dirname($path).'/.meta/'.basename($path).'.yaml',
            YAML::dump(['width' => 20, 'height' => 10])
        );
    }

    #[Test]
    public function it_generates_all_presets_when_no_filter_is_given()
    {
        $this->makeContainer();
        $this->putLandscapeImage();

        $this->artisan('statamic:assets:generate-presets');

        Bus::assertDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'preset_one');
        Bus::assertDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'preset_two');
        Bus::assertDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'cp_thumbnail_small_landscape');
    }

    #[Test]
    public function it_only_generates_cp_thumbnail_presets_when_cp_thumbnail_filter_is_given()
    {
        $this->makeContainer();
        $this->putLandscapeImage();

        $this->artisan('statamic:assets:generate-presets --preset=cp_thumbnail');

        Bus::assertDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'cp_thumbnail_small_landscape');
        Bus::assertNotDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'preset_one');
        Bus::assertNotDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'preset_two');
    }

    #[Test]
    public function it_generates_nothing_when_cp_thumbnail_filter_is_used_but_cp_is_disabled()
    {
        config(['statamic.cp.enabled' => false]);

        $this->makeContainer();
        $this->putLandscapeImage();

        $this->artisan('statamic:assets:generate-presets --preset=cp_thumbnail');

        Bus::assertNotDispatchedSync(GeneratePresetImageManipulation::class);
    }

    #[Test]
    public function it_generates_only_the_specified_preset()
    {
        $this->makeContainer();
        $this->putLandscapeImage();

        $this->artisan('statamic:assets:generate-presets --preset=preset_one');

        Bus::assertDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'preset_one');
        Bus::assertNotDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'preset_two');
        Bus::assertNotDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'cp_thumbnail_small_landscape');
    }

    #[Test]
    public function it_errors_when_the_preset_does_not_exist()
    {
        $this->makeContainer();
        $this->putLandscapeImage();

        $this->artisan('statamic:assets:generate-presets --preset=nonexistent')
            ->expectsOutputToContain('The preset "nonexistent" does not exist.')
            ->assertExitCode(1);

        Bus::assertNotDispatchedSync(GeneratePresetImageManipulation::class);
    }

    #[Test]
    public function it_skips_non_image_assets()
    {
        $this->makeContainer();
        Storage::disk('test')->put('document.txt', 'hello');

        $this->artisan('statamic:assets:generate-presets');

        Bus::assertNotDispatchedSync(GeneratePresetImageManipulation::class);
    }

    #[Test]
    public function it_queues_jobs_when_queue_option_is_used()
    {
        config(['queue.default' => 'redis']);

        $this->makeContainer('test', ['preset_one']);
        $this->putLandscapeImage();

        $this->artisan('statamic:assets:generate-presets --queue');

        Bus::assertDispatched(GeneratePresetImageManipulation::class, fn ($job) => $job->preset === 'preset_one');
        Bus::assertNotDispatchedSync(GeneratePresetImageManipulation::class);
    }

    #[Test]
    public function it_excludes_specified_containers()
    {
        Storage::fake('other');

        $this->makeContainer('test', ['preset_one']);
        AssetContainer::make('excluded')->disk('other')->warmPresets(['preset_one'])->save();

        $this->putLandscapeImage();
        Storage::disk('other')->put('image.jpg', '');
        Storage::disk('other')->put('.meta/image.jpg.yaml', YAML::dump(['width' => 20, 'height' => 10]));

        $this->artisan('statamic:assets:generate-presets --excluded-containers=excluded');

        Bus::assertDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->asset->container()->handle() === 'test');
        Bus::assertNotDispatchedSync(GeneratePresetImageManipulation::class, fn ($job) => $job->asset->container()->handle() === 'excluded');
    }
}
