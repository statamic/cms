<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\SingleAsset;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SingleAssetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Storage::fake('test', ['url' => '/assets']);
        \Illuminate\Support\Facades\Storage::disk('test')->put('foo/one.txt', '');

        AssetContainer::make('test')->disk('test')->save();
    }

    #[Test]
    public function it_preprocesses_a_stored_path_as_a_single_item_array()
    {
        $processed = $this->fieldtype()->preProcess('foo/one.txt');

        $this->assertEquals(['test::foo/one.txt'], $processed);
    }

    #[Test]
    public function it_processes_to_an_array_for_nested_config_storage()
    {
        $processed = $this->fieldtype()->process(['test::foo/one.txt']);

        $this->assertEquals(['foo/one.txt'], $processed);
    }

    private function fieldtype(): SingleAsset
    {
        return (new SingleAsset)->setField(new Field('image', [
            'type' => 'single_asset',
            'max_files' => 1,
            'container' => 'test',
        ]));
    }
}
