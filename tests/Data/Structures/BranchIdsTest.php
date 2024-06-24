<?php

namespace Tests\Data\Structures;

use Facades\Statamic\Structures\BranchIdGenerator;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Structures\BranchIds;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class BranchIdsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_adds_ids_to_branches()
    {
        $tree = [
            ['title' => 'First'],
            ['title' => 'Second', 'children' => [
                ['id' => 'existing-id', 'title' => 'Child 1'],
                ['title' => 'Child 2', 'children' => [
                    ['title' => 'Grandchild 1'],
                    ['id' => 'another-existing-id', 'entry' => 'a'],
                    ['title' => 'Grandchild 3'],
                ]],
                ['entry' => 'b'],
                ['title' => 'Child 4'],
            ]],
        ];

        BranchIdGenerator::shouldReceive('generate')->times(7)->andReturn(
            'id1', 'id2', 'id3', 'id4', 'id5', 'id6', 'id7'
        );

        $ensured = (new BranchIds)->ensure($tree);

        $this->assertSame([
            ['id' => 'id1', 'title' => 'First'],
            ['id' => 'id2', 'title' => 'Second', 'children' => [
                ['id' => 'existing-id', 'title' => 'Child 1'],
                ['id' => 'id3', 'title' => 'Child 2', 'children' => [
                    ['id' => 'id4', 'title' => 'Grandchild 1'],
                    ['id' => 'another-existing-id', 'entry' => 'a'],
                    ['id' => 'id5', 'title' => 'Grandchild 3'],
                ]],
                ['id' => 'id6', 'entry' => 'b'],
                ['id' => 'id7', 'title' => 'Child 4'],
            ]],
        ], $ensured);
    }
}
