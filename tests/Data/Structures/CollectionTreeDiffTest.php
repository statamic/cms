<?php

namespace Tests\Data\Structures;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Structures\CollectionTreeDiff;
use Tests\TestCase;

class CollectionTreeDiffTest extends TestCase
{
    #[Test]
    public function it_sees_no_changes()
    {
        $old = [];
        $new = [];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertFalse($analyzer->hasChanged());
        $this->assertEquals([], $analyzer->affected());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals([], $analyzer->moved());
        $this->assertEquals([], $analyzer->ancestryChanged());
    }

    #[Test]
    public function it_sees_additions()
    {
        $old = [];
        $new = [
            ['entry' => '1', 'a' => 'b'],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['1'], $analyzer->affected());
        $this->assertEquals(['1'], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals([], $analyzer->moved());
        $this->assertEquals([], $analyzer->ancestryChanged());
    }

    #[Test]
    public function it_sees_removals()
    {
        $old = [
            ['entry' => '1', 'a' => 'b'],
        ];
        $new = [];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['1'], $analyzer->affected());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals(['1'], $analyzer->removed());
        $this->assertEquals([], $analyzer->moved());
        $this->assertEquals([], $analyzer->ancestryChanged());
    }

    #[Test]
    public function it_sees_moves()
    {
        // Simply moving 2 after 4 will consider 2,3,4 all moved because their indexes change.

        $old = [
            ['entry' => '1', 'a' => 'b'],
            ['entry' => '2', 'c' => 'd'],
            ['entry' => '3', 'e' => 'f'],
            ['entry' => '4', 'g' => 'h'],
            ['entry' => '5', 'i' => 'j'],
        ];
        $new = [
            ['entry' => '1', 'a' => 'b'],
            ['entry' => '3', 'e' => 'f'],
            ['entry' => '4', 'g' => 'h'],
            ['entry' => '2', 'c' => 'd'],
            ['entry' => '5', 'i' => 'j'],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['2', '3', '4'], $analyzer->affected());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals(['2', '3', '4'], $analyzer->moved());
        $this->assertEquals([], $analyzer->ancestryChanged());
    }

    #[Test]
    public function it_sees_moves_within_a_branch()
    {
        // Simply moving 12 after 14 will consider 12,13,14 all moved because their indexes change.

        $old = [
            ['entry' => '1'],
            ['entry' => '2', 'children' => [
                ['entry' => '11'],
                ['entry' => '12'],
                ['entry' => '13'],
                ['entry' => '14'],
                ['entry' => '15'],
            ]],
            ['entry' => '3'],
        ];
        $new = [
            ['entry' => '1'],
            ['entry' => '2', 'children' => [
                ['entry' => '11'],
                ['entry' => '13'],
                ['entry' => '14'],
                ['entry' => '12'],
                ['entry' => '15'],
            ]],
            ['entry' => '3'],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['12', '13', '14'], $analyzer->affected());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals(['12', '13', '14'], $analyzer->moved());
        $this->assertEquals([], $analyzer->ancestryChanged());
    }

    #[Test]
    public function it_sees_additions_and_removals()
    {
        $old = [
            ['entry' => '1', 'a' => 'b'],
        ];
        $new = [
            ['entry' => '2', 'c' => 'd'],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['1', '2'], $analyzer->affected());
        $this->assertEquals(['2'], $analyzer->added());
        $this->assertEquals(['1'], $analyzer->removed());
        $this->assertEquals([], $analyzer->ancestryChanged());
    }

    #[Test]
    public function it_sees_multilevel_changes()
    {
        $old = [
            ['entry' => '1', 'a' => 'b', 'children' => [
                ['entry' => '6', 'q' => 'r'],
                ['entry' => '16', 'w' => 'x'],
                ['entry' => '61', 'y' => 'z'],
                ['entry' => '7', 'c' => 'd'],
                ['entry' => '8', 'e' => 'f'],
                ['entry' => '10', 'g' => 'h'],
            ]],
            ['entry' => '2', 'i' => 'j', 'children' => [
                ['entry' => '3', 'k' => 'l'],
                ['entry' => '13', 'm' => 'n'],
            ]],
        ];

        $new = [
            ['entry' => '1', 'a' => 'b', 'children' => [
                ['entry' => '6', 'q' => 'r'],
                ['entry' => '61', 'y' => 'z'],
                ['entry' => '16', 'w' => 'x'],
            ]],
            ['entry' => '2', 'i' => 'j', 'children' => [
                ['entry' => '3', 'k' => 'l'],
                ['entry' => '13', 'm' => 'n'],
                ['entry' => '10', 'g' => 'h'],
            ]],
            ['entry' => '9', 'o' => 'p'],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals(['9'], $analyzer->added());
        $this->assertEquals(['7', '8'], $analyzer->removed());
        $this->assertEquals(['16', '61', '10'], $analyzer->moved());
        $this->assertEquals(['7', '8', '9', '16', '61', '10'], $analyzer->affected());
        $this->assertEquals(['10'], $analyzer->ancestryChanged());
    }

    #[Test]
    public function movement_of_an_ancestor_does_not_cause_a_child_to_be_considered_moved()
    {
        // Entry 5 is what this test is really all about.
        // Moving 2 will cause 4 (5's parent) to be moved, but we want to make sure 5 isn't considered moved.

        $old = [
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
            ['entry' => '4', 'children' => [
                ['entry' => '5'],
                ['entry' => '6', 'children' => [
                    ['entry' => '7'],
                ]],
            ]],
        ];

        $new = [
            ['entry' => '1'],                    // it's still 1st
            ['entry' => '3'],                    // moved from 3rd to 1st
            ['entry' => '4', 'children' => [     // moved from 4th to 3rd
                ['entry' => '5'],                // it's still 1st under entry '4'
                ['entry' => '2'],                // it was moved from the parent level
                ['entry' => '6', 'children' => [ // moved from 2nd to 3rd
                    ['entry' => '7'],            // it's still 1st under entry '6', under entry '4'
                ]],
            ]],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals([2, 3, 4, 6], $analyzer->moved());
        $this->assertEquals([2, 3, 4, 6], $analyzer->affected());
        $this->assertEquals([2], $analyzer->ancestryChanged());
    }

    #[Test]
    public function moving_a_top_level_item_to_the_start_while_expecting_a_root_will_consider_it_an_ancestry_change()
    {
        $old = [
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
        ];

        $new = [
            ['entry' => '2'],
            ['entry' => '1'],
            ['entry' => '3'],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new, true);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals([1, 2], $analyzer->moved());
        $this->assertEquals([1, 2], $analyzer->affected());
        $this->assertEquals([1, 2], $analyzer->ancestryChanged());
    }

    #[Test]
    public function moving_a_top_level_item_to_the_start_while_not_expecting_a_root_will_not_consider_it_an_ancestry_change()
    {
        $old = [
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
        ];

        $new = [
            ['entry' => '2'],
            ['entry' => '1'],
            ['entry' => '3'],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals([1, 2], $analyzer->moved());
        $this->assertEquals([1, 2], $analyzer->affected());
        $this->assertEquals([], $analyzer->ancestryChanged());
    }

    #[Test]
    public function moving_a_nested_item_to_the_start_while_expecting_a_root_will_not_consider_it_an_ancestry_change()
    {
        $old = [
            ['entry' => '1', 'children' => [
                ['entry' => '2'],
                ['entry' => '3'],
            ]],
        ];

        $new = [
            ['entry' => '1', 'children' => [
                ['entry' => '3'],
                ['entry' => '2'],
            ]],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new, true);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals([2, 3], $analyzer->moved());
        $this->assertEquals([2, 3], $analyzer->affected());
        $this->assertEquals([], $analyzer->ancestryChanged());
    }

    #[Test]
    public function moving_a_nested_item_to_the_start_while_not_expecting_a_root_will_not_consider_it_an_ancestry_change()
    {
        $old = [
            ['entry' => '1', 'children' => [
                ['entry' => '2'],
                ['entry' => '3'],
            ]],
        ];

        $new = [
            ['entry' => '1', 'children' => [
                ['entry' => '3'],
                ['entry' => '2'],
            ]],
        ];

        $analyzer = (new CollectionTreeDiff)->analyze($old, $new);

        $this->assertTrue($analyzer->hasChanged());
        $this->assertEquals([], $analyzer->added());
        $this->assertEquals([], $analyzer->removed());
        $this->assertEquals([2, 3], $analyzer->moved());
        $this->assertEquals([2, 3], $analyzer->affected());
        $this->assertEquals([], $analyzer->ancestryChanged());
    }
}
