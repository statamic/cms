<?php

namespace Tests\UpdateScripts;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\UpdateScripts\RemoveParentField;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UpdateScripts\Concerns\RunsUpdateScripts;

class RemoveParentFieldTest extends TestCase
{
    use PreventSavingStacheItemsToDisk, RunsUpdateScripts;

    #[Test]
    public function it_is_registered()
    {
        $this->assertUpdateScriptRegistered(RemoveParentField::class);
    }

    #[Test]
    public function it_removes_parent_field_from_structured_collection_blueprint()
    {
        $collection = tap(Collection::make('test')->structureContents(['tree' => []]))->save();

        $blueprint = $collection->entryBlueprint();

        $blueprint->setContents(['tabs' => [
            'main' => ['sections' => [
                ['fields' => [
                    ['handle' => 'title', 'field' => ['type' => 'text']],
                ]],
            ]],
            'sidebar' => ['sections' => [
                ['fields' => [
                    ['handle' => 'slug', 'field' => ['type' => 'slug']],
                    ['handle' => 'parent', 'field' => ['type' => 'entries', 'collections' => ['test'], 'max_items' => 1]],
                ]],
            ]],
        ]]);

        $blueprint->save();

        $this->runUpdateScript(RemoveParentField::class);

        $blueprint = Blueprint::find($blueprint->fullyQualifiedHandle());

        $this->assertFalse($blueprint->hasField('parent'));
    }

    #[Test]
    public function it_does_not_remove_parent_field_from_unstructured_collection_blueprint()
    {
        $collection = tap(Collection::make('test'))->save();

        $blueprint = $collection->entryBlueprint();

        $blueprint->setContents(['tabs' => [
            'main' => ['sections' => [
                ['fields' => [
                    ['handle' => 'title', 'field' => ['type' => 'text']],
                ]],
            ]],
            'sidebar' => ['sections' => [
                ['fields' => [
                    ['handle' => 'slug', 'field' => ['type' => 'slug']],
                    ['handle' => 'parent', 'field' => ['type' => 'entries', 'collections' => ['test'], 'max_items' => 1]],
                ]],
            ]],
        ]]);

        $blueprint->save();

        $this->runUpdateScript(RemoveParentField::class);

        $blueprint = Blueprint::find($blueprint->fullyQualifiedHandle());

        $this->assertTrue($blueprint->hasField('parent'));
    }
}
