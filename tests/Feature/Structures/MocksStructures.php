<?php

namespace Tests\Feature\Structures;

use Mockery;
use Statamic\Contracts\Structures\Structure;
use Statamic\Structures\Tree;

trait MocksStructures
{
    private function createStructure($handle)
    {
        return tap(Mockery::mock(Structure::class), function ($s) use ($handle) {
            $s->shouldReceive('in')->andReturn($this->createStructureTree($handle));
            $s->shouldReceive('title')->andReturn($handle);
            $s->shouldReceive('handle')->andReturn($handle);
            $s->shouldReceive('editUrl')->andReturn('/structure-edit-url');
            $s->shouldReceive('deleteUrl')->andReturn('/structure-delete-url');
        });
    }

    private function createNavStructure($structure)
    {
        return tap($this->createStructure($structure), function ($s) {
            $s->shouldReceive('collection')->andReturnNull();
            $s->shouldReceive('isCollectionBased')->andReturnFalse();
            $s->shouldReceive('collections')->andReturn(collect());
            $s->shouldReceive('expectsRoot')->andReturnTrue();
        });
    }

    private function createCollectionStructure($structure)
    {
        return tap($this->createStructure($structure), function ($s) {
            $s->shouldReceive('collection')->andReturn(true); // should return a collection instance but we're not using it in tests yet
            $s->shouldReceive('isCollectionBased')->andReturnTrue();
        });
    }

    private function createStructureTree($handle)
    {
        return tap(Mockery::mock(Tree::class), function ($s) use ($handle) {
            $s->shouldReceive('editUrl')->andReturn('/tree-edit-url');
            $s->shouldReceive('route')->andReturn('/route');
        });
    }
}
