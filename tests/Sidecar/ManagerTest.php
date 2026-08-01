<?php

namespace Tests\Sidecar;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry;
use Statamic\Entries\Collection;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Facades\Sidecar;
use Statamic\Fields\Blueprint as BlueprintInstance;
use Statamic\Sidecar\Drivers\Driver;
use Statamic\Sidecar\Manager;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private Manager $manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = new Manager;

        $this->manager->extend('fake', function ($app, $config, $handle) {
            return new class($config, $handle) extends Driver
            {
                public function title(): string
                {
                    return 'Fake Docs';
                }

                protected function defaultBlueprint(): BlueprintInstance
                {
                    return $this->makeBlueprint([
                        'title' => 'Fake Doc',
                        'fields' => [
                            ['handle' => 'content', 'field' => ['type' => 'markdown']],
                            ['handle' => 'order', 'field' => ['type' => 'integer']],
                        ],
                    ]);
                }

                public function afterSave(Entry $entry): void
                {
                    $entry->set('saved_hook', true);
                }

                public function previewUrl(Entry $entry): ?string
                {
                    return url('fake-docs/'.$entry->slug());
                }
            };
        });

        $this->app->instance(Manager::class, $this->manager);
    }

    #[Test]
    public function it_registers_collections_from_config()
    {
        config(['statamic.sidecar.collections' => [
            'docs' => [
                'driver' => 'fake',
                'directory' => $this->fakeStacheDirectory.'/docs',
            ],
        ]]);

        $this->manager->boot();

        $collection = CollectionAPI::findByHandle('docs');

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals('Fake Docs', $collection->title());
        $this->assertEquals($this->fakeStacheDirectory.'/docs', $collection->directory());
        $this->assertTrue($this->manager->manages('docs'));
        $this->assertFalse(file_exists($this->fakeStacheDirectory.'/content/collections/docs.yaml'));
    }

    #[Test]
    public function it_uses_config_title_override()
    {
        config(['statamic.sidecar.collections' => [
            'docs' => [
                'driver' => 'fake',
                'directory' => $this->fakeStacheDirectory.'/docs',
                'title' => 'Documentation',
            ],
        ]]);

        $this->manager->boot();

        $this->assertEquals('Documentation', CollectionAPI::findByHandle('docs')->title());
    }

    #[Test]
    public function it_provides_a_fallback_blueprint()
    {
        config(['statamic.sidecar.collections' => [
            'docs' => [
                'driver' => 'fake',
                'directory' => $this->fakeStacheDirectory.'/docs',
            ],
        ]]);

        $this->manager->boot();

        $blueprint = CollectionAPI::findByHandle('docs')->entryBlueprint();

        $this->assertInstanceOf(BlueprintInstance::class, $blueprint);
        $this->assertTrue($blueprint->fields()->all()->has('content'));
        $this->assertTrue($blueprint->fields()->all()->has('order'));
    }

    #[Test]
    public function it_can_extend_custom_drivers()
    {
        $this->assertTrue(Sidecar::hasDriver('fake'));
        $this->assertContains('fake', Sidecar::registeredDrivers());
    }

    #[Test]
    public function it_resolves_entry_urls_from_driver_preview_url()
    {
        config(['statamic.sidecar.collections' => [
            'docs' => [
                'driver' => 'fake',
                'directory' => $this->fakeStacheDirectory.'/docs',
            ],
        ]]);

        $this->manager->boot();

        $entry = \Statamic\Facades\Entry::make()
            ->collection('docs')
            ->id('doc-1')
            ->slug('getting-started')
            ->data(['title' => 'Getting Started']);

        $this->assertEquals('/fake-docs/getting-started', $entry->uri());
        $this->assertEquals('/fake-docs/getting-started', $entry->url());
        $this->assertNotNull($entry->absoluteUrl());
        $this->assertStringEndsWith('/fake-docs/getting-started', $entry->absoluteUrl());
    }
}
