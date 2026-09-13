<?php

namespace Tests\Data;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Facades;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\UpdatesReferences;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DataReferenceUpdaterTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        ParentRecorderFieldtype::register();
        ParentRecorderFieldtype::$parents = [];

        tap(Facades\Collection::make('articles'))->save();
    }

    #[Test]
    public function it_gives_top_level_fields_the_item_being_updated_as_their_parent()
    {
        $this->setInBlueprints('collections/articles', [
            'fields' => [
                ['handle' => 'hero', 'field' => ['type' => 'parent_recorder']],
            ],
        ]);

        $one = tap(Facades\Entry::make()->collection('articles')->slug('one')->data(['hero' => 'hoff.jpg']))->save();
        $two = tap(Facades\Entry::make()->collection('articles')->slug('two')->data(['hero' => 'hoff.jpg']))->save();

        $this->updateReferences($one);
        $this->updateReferences($two);

        $this->assertCount(2, ParentRecorderFieldtype::$parents);
        $this->assertSame($one, ParentRecorderFieldtype::$parents[0]);
        $this->assertSame($two, ParentRecorderFieldtype::$parents[1]);
    }

    #[Test]
    public function it_gives_nested_fields_the_item_being_updated_as_their_parent()
    {
        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'grid',
                    'field' => [
                        'type' => 'grid',
                        'fields' => [
                            ['handle' => 'hero', 'field' => ['type' => 'parent_recorder']],
                        ],
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection('articles')->slug('one')->data([
            'grid' => [['hero' => 'hoff.jpg']],
        ]))->save();

        $this->updateReferences($entry);

        $this->assertCount(1, ParentRecorderFieldtype::$parents);
        $this->assertSame($entry, ParentRecorderFieldtype::$parents[0]);
    }

    #[Test]
    public function it_doesnt_leave_the_item_on_the_blueprints_shared_fields()
    {
        $this->setInBlueprints('collections/articles', [
            'fields' => [
                ['handle' => 'hero', 'field' => ['type' => 'parent_recorder']],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection('articles')->slug('one')->data(['hero' => 'hoff.jpg']))->save();

        $blueprint = Facades\Collection::find('articles')->entryBlueprint();
        $blueprint->setParent(null);

        $this->updateReferences($entry);

        $this->assertNull($blueprint->fields()->get('hero')->parent());
    }

    private function setInBlueprints($namespace, $blueprintContents)
    {
        $blueprint = tap(Facades\Blueprint::make('set-in-blueprints')->setContents($blueprintContents))->save();

        Facades\Blueprint::shouldReceive('in')->with($namespace)->andReturn(collect([$blueprint]));
    }

    private function updateReferences($item)
    {
        AssetReferenceUpdater::item($item)
            ->filterByContainer('test_container')
            ->updateReferences('hoff.jpg', 'norris.jpg');
    }
}

class ParentRecorderFieldtype extends Fieldtype
{
    use UpdatesReferences;

    public static $parents = [];

    public function replaceAssetReferences($data, ?string $newValue, string $oldValue, string $container)
    {
        static::$parents[] = $this->field->parent();

        return $data;
    }
}
