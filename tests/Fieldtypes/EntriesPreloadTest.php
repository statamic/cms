<?php

namespace Tests\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Entries;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntriesPreloadTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $collection = tap(Facades\Collection::make('pages')->routes('{parent_uri}/{slug}'))->save();

        EntryFactory::id('e1')->collection($collection)->slug('a')->create();

        $collection->structureContents(['root' => false])->save();
        $collection->structure()->in('en')->tree([['entry' => 'e1']])->save();
    }

    #[Test]
    public function it_preloads_the_tree_when_you_can_view_the_collection()
    {
        $this->setTestRoles(['test' => ['access cp', 'view pages entries']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $this->actingAs($user);

        $this->assertArrayHasKey('tree', $this->fieldtype()->preload());
    }

    #[Test]
    public function it_doesnt_preload_the_tree_when_you_cant_view_the_collection()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $this->actingAs($user);

        $this->assertArrayNotHasKey('tree', $this->fieldtype()->preload());
    }

    private function fieldtype()
    {
        return (new Entries)->setField(new Field('test', [
            'type' => 'entries',
            'collections' => ['pages'],
        ]));
    }
}
