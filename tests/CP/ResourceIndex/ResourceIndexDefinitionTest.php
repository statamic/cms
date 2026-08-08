<?php

namespace Tests\CP\ResourceIndex;

use Illuminate\Container\Container;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Statamic\CP\ResourceIndex\ResourceIndex;

class ResourceIndexDefinitionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $app = new Container;
        $app->instance('translator', new Translator(new ArrayLoader, 'en'));
        Container::setInstance($app);
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);

        parent::tearDown();
    }

    #[Test]
    public function it_normalizes_items_and_provides_named_defaults()
    {
        $index = new ResourceIndex('ships', [
            ['id' => 'falcon', 'title' => 'Millennium Falcon'],
        ]);

        $this->assertSame('ships', $index->handle());
        $this->assertSame('Ships', $index->title());
        $this->assertSame('Ship', $index->itemLabel());
        $this->assertSame('ships', $index->icon());
        $this->assertSame('falcon', $index->all()->first()['id']);
    }

    #[Test]
    public function it_rejects_items_without_the_required_identity_fields()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('require id and title values');

        new ResourceIndex('ships', [['id' => 'falcon']]);
    }

    #[Test]
    public function default_groups_may_be_derived_from_the_local_items()
    {
        $index = (new ResourceIndex('ships', [
            ['id' => 'falcon', 'title' => 'Millennium Falcon'],
        ]))->defaultGroups(fn ($items) => [[
            'id' => 'rebels',
            'title' => 'Rebels',
            'items' => $items->pluck('id')->all(),
        ]]);

        $this->assertSame(['falcon'], $index->defaultGroups()[0]['items']);
    }
}
