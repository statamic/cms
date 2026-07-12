<?php

namespace Tests\Data\Structures;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

abstract class StructureTestCase extends TestCase
{
    abstract public function structure($handle = null);

    #[Test]
    public function the_tree_root_cannot_have_children_when_expecting_root()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Root page cannot have children');

        $this->structure()->expectsRoot(true)->validateTree([
            [
                'entry' => '123',
                'children' => [
                    [
                        'entry' => '456',
                    ],
                ],
            ],
        ], 'en');
    }

    #[Test]
    public function the_tree_root_can_have_children_when_not_expecting_root()
    {
        $tree = [
            [
                'entry' => '123',
                'children' => [
                    [
                        'entry' => '456',
                    ],
                ],
            ],
        ];

        $this->assertEquals($tree, $this->structure('test')->expectsRoot(false)->validateTree($tree, 'en'));
    }

    #[Test]
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $structure = $this->structure('test');

        $structure
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $structure->{$key}));
    }
}
