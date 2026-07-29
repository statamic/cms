<?php

namespace Tests\Data;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Facades\Blueprint;
use Tests\TestCase;

class DataReferenceUpdaterTest extends TestCase
{
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

                return Blueprint::makeFromFields([]);
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
}
