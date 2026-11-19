<?php

namespace Tests\Data;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Facades;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\UpdatesReferences;
use Statamic\Taxonomies\TermReferenceUpdater;
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

    private function makeItem(array $data)
    {
        return new class($data)
        {
            public $blueprintResolved = false;

            public function __construct(private $data)
            {
                $this->data = collect($data);
            }

            public function data()
            {
                return $this->data;
            }

            public function blueprint()
            {
                $this->blueprintResolved = true;

                return Facades\Blueprint::makeFromFields([]);
            }

            public function save()
            {
                //
            }
        };
    }

    #[Test]
    public function it_skips_blueprint_traversal_when_data_cannot_contain_the_original_value()
    {
        $item = $this->makeItem(['hero' => 'unrelated.jpg']);

        $updated = AssetReferenceUpdater::item($item)
            ->filterByContainer('assets')
            ->updateReferences('img/hoff.jpg', 'img/new-hoff.jpg');

        $this->assertFalse($updated);
        $this->assertFalse($item->blueprintResolved);
    }

    #[Test]
    public function it_traverses_blueprint_when_data_contains_the_original_value()
    {
        $item = $this->makeItem(['hero' => 'img/hoff.jpg']);

        AssetReferenceUpdater::item($item)
            ->filterByContainer('assets')
            ->updateReferences('img/hoff.jpg', 'img/new-hoff.jpg');

        $this->assertTrue($item->blueprintResolved);
    }

    #[Test]
    public function it_traverses_blueprint_when_original_value_appears_within_a_larger_string()
    {
        $item = $this->makeItem(['content' => '[link](statamic://asset::assets::img/hoff.jpg)']);

        AssetReferenceUpdater::item($item)
            ->filterByContainer('assets')
            ->updateReferences('img/hoff.jpg', 'img/new-hoff.jpg');

        $this->assertTrue($item->blueprintResolved);
    }

    #[Test]
    public function it_traverses_blueprint_when_original_value_contains_non_ascii_characters()
    {
        $item = $this->makeItem(['hero' => 'img/föö-bär.jpg']);

        AssetReferenceUpdater::item($item)
            ->filterByContainer('assets')
            ->updateReferences('img/föö-bär.jpg', 'img/new.jpg');

        $this->assertTrue($item->blueprintResolved);
    }

    #[Test]
    public function it_traverses_blueprint_when_original_value_contains_json_special_characters()
    {
        $item = $this->makeItem(['hero' => 'img/we"ird\\file.jpg']);

        AssetReferenceUpdater::item($item)
            ->filterByContainer('assets')
            ->updateReferences('img/we"ird\\file.jpg', 'img/new.jpg');

        $this->assertTrue($item->blueprintResolved);
    }

    #[Test]
    public function it_traverses_blueprint_when_json_encoding_data_throws()
    {
        $throwing = new class implements \JsonSerializable
        {
            public function jsonSerialize(): mixed
            {
                throw new \Exception('Cannot be serialized.');
            }
        };

        $item = $this->makeItem(['object' => $throwing, 'hero' => 'unrelated.jpg']);

        AssetReferenceUpdater::item($item)
            ->filterByContainer('assets')
            ->updateReferences('img/hoff.jpg', 'img/new-hoff.jpg');

        $this->assertTrue($item->blueprintResolved);
    }

    #[Test]
    public function it_traverses_blueprint_when_data_cannot_be_json_encoded()
    {
        $item = $this->makeItem(['broken' => "\xB1\x31", 'hero' => 'unrelated.jpg']);

        AssetReferenceUpdater::item($item)
            ->filterByContainer('assets')
            ->updateReferences('img/hoff.jpg', 'img/new-hoff.jpg');

        $this->assertTrue($item->blueprintResolved);
    }

    #[Test]
    public function it_traverses_blueprint_when_original_value_cannot_be_json_encoded()
    {
        $item = $this->makeItem(['hero' => 'unrelated.jpg']);

        AssetReferenceUpdater::item($item)
            ->filterByContainer('assets')
            ->updateReferences("img/\xB1\x31.jpg", 'img/new.jpg');

        $this->assertTrue($item->blueprintResolved);
    }

    #[Test]
    public function it_traverses_blueprint_when_original_value_is_nested_within_replicator_and_grid_data()
    {
        $item = $this->makeItem([
            'sets' => [
                ['type' => 'image', 'grid' => [['hero' => 'img/hoff.jpg']]],
            ],
        ]);

        AssetReferenceUpdater::item($item)
            ->filterByContainer('assets')
            ->updateReferences('img/hoff.jpg', 'img/new-hoff.jpg');

        $this->assertTrue($item->blueprintResolved);
    }

    #[Test]
    public function it_skips_blueprint_traversal_for_terms_when_data_cannot_contain_the_original_value()
    {
        $item = $this->makeItem(['tags' => ['other']]);

        $updated = TermReferenceUpdater::item($item)
            ->filterByTaxonomy('tags')
            ->updateReferences('rad', 'radical');

        $this->assertFalse($updated);
        $this->assertFalse($item->blueprintResolved);
    }

    #[Test]
    public function it_traverses_blueprint_for_terms_when_data_contains_the_original_value()
    {
        $item = $this->makeItem(['tags' => ['rad']]);

        TermReferenceUpdater::item($item)
            ->filterByTaxonomy('tags')
            ->updateReferences('rad', 'radical');

        $this->assertTrue($item->blueprintResolved);
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
