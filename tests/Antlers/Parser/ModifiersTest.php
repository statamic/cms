<?php

namespace Tests\Antlers\Parser;

use Statamic\View\Antlers\Language\Nodes\ModifierNameNode;
use Statamic\View\Antlers\Language\Nodes\Modifiers\ModifierNode;
use Statamic\View\Antlers\Language\Nodes\ModifierValueNode;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\SemanticGroup;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Tests\Antlers\ParserTestCase;

class ModifiersTest extends ParserTestCase
{
    private function assertModifierName($name, ModifierNode $node)
    {
        $this->assertNotNull($node->nameNode);
        $this->assertInstanceOf(ModifierNameNode::class, $node->nameNode);
        $this->assertEquals($name, $node->nameNode->name);
    }

    public function test_it_parses_node_modifiers()
    {
        $nodes = $this->getParsedRuntimeNodes('{{ title | upper | lower }}');
        $this->assertInstanceOf(SemanticGroup::class, $nodes[0]);

        /** @var SemanticGroup $semanticGroup */
        $semanticGroup = $nodes[0];
        $this->assertCount(1, $semanticGroup->nodes);

        /** @var VariableNode $varNode */
        $varNode = $semanticGroup->nodes[0];
        $this->assertInstanceOf(VariableNode::class, $varNode);
        $this->assertSame('title', $varNode->name);

        $this->assertNotNull($varNode->modifierChain);

        $chain = $varNode->modifierChain;

        $this->assertCount(2, $chain->modifierChain);

        $modifierOne = $chain->modifierChain[0];
        $modifierTwo = $chain->modifierChain[1];

        $this->assertModifierName('upper', $modifierOne);
        $this->assertModifierName('lower', $modifierTwo);
    }

    public function test_modifiers_with_underscores()
    {
        $nodes = $this->getParsedRuntimeNodes('{{ title | test_param:test:param:"hello :|" | lower }}');
        $this->assertInstanceOf(SemanticGroup::class, $nodes[0]);

        /** @var SemanticGroup $semanticGroup */
        $semanticGroup = $nodes[0];
        $this->assertCount(1, $semanticGroup->nodes);

        /** @var VariableNode $varNode */
        $varNode = $semanticGroup->nodes[0];
        $this->assertInstanceOf(VariableNode::class, $varNode);
        $this->assertSame('title', $varNode->name);

        $this->assertNotNull($varNode->modifierChain);

        $chain = $varNode->modifierChain;

        $this->assertCount(2, $chain->modifierChain);

        $modifierOne = $chain->modifierChain[0];
        $modifierTwo = $chain->modifierChain[1];

        $this->assertModifierName('test_param', $modifierOne);
        $this->assertModifierName('lower', $modifierTwo);

        $this->assertCount(3, $modifierOne->valueNodes);

        /** @var ModifierValueNode $valueOne */
        $valueOne = $modifierOne->valueNodes[0];
        $this->assertInstanceOf(ModifierValueNode::class, $valueOne);
        $this->assertSame('test', $valueOne->value);

        /** @var ModifierValueNode $valueTwo */
        $valueTwo = $modifierOne->valueNodes[1];
        $this->assertInstanceOf(ModifierValueNode::class, $valueTwo);
        $this->assertSame('param', $valueTwo->value);

        /** @var StringValueNode $valueThree */
        $valueThree = $modifierOne->valueNodes[2];
        $this->assertInstanceOf(StringValueNode::class, $valueThree);
        $this->assertSame('hello :|', $valueThree->value);
    }

    public function test_modifiers_with_hyphens()
    {
        $nodes = $this->getParsedRuntimeNodes('{{ title | test-param:test:param:"hello :|" | lower }}');
        $this->assertInstanceOf(SemanticGroup::class, $nodes[0]);

        /** @var SemanticGroup $semanticGroup */
        $semanticGroup = $nodes[0];
        $this->assertCount(1, $semanticGroup->nodes);

        /** @var VariableNode $varNode */
        $varNode = $semanticGroup->nodes[0];
        $this->assertInstanceOf(VariableNode::class, $varNode);
        $this->assertSame('title', $varNode->name);

        $this->assertNotNull($varNode->modifierChain);

        $chain = $varNode->modifierChain;

        $this->assertCount(2, $chain->modifierChain);

        $modifierOne = $chain->modifierChain[0];
        $modifierTwo = $chain->modifierChain[1];

        $this->assertModifierName('test-param', $modifierOne);
        $this->assertModifierName('lower', $modifierTwo);

        $this->assertCount(3, $modifierOne->valueNodes);

        /** @var ModifierValueNode $valueOne */
        $valueOne = $modifierOne->valueNodes[0];
        $this->assertInstanceOf(ModifierValueNode::class, $valueOne);
        $this->assertSame('test', $valueOne->value);

        /** @var ModifierValueNode $valueTwo */
        $valueTwo = $modifierOne->valueNodes[1];
        $this->assertInstanceOf(ModifierValueNode::class, $valueTwo);
        $this->assertSame('param', $valueTwo->value);

        /** @var StringValueNode $valueThree */
        $valueThree = $modifierOne->valueNodes[2];
        $this->assertInstanceOf(StringValueNode::class, $valueThree);
        $this->assertSame('hello :|', $valueThree->value);
    }

    public function test_it_parses_modifier_values()
    {
        $nodes = $this->getParsedRuntimeNodes('{{ title | upper:test:param:"hello :|" | lower }}');
        $this->assertInstanceOf(SemanticGroup::class, $nodes[0]);

        /** @var SemanticGroup $semanticGroup */
        $semanticGroup = $nodes[0];
        $this->assertCount(1, $semanticGroup->nodes);

        /** @var VariableNode $varNode */
        $varNode = $semanticGroup->nodes[0];
        $this->assertInstanceOf(VariableNode::class, $varNode);
        $this->assertSame('title', $varNode->name);

        $this->assertNotNull($varNode->modifierChain);

        $chain = $varNode->modifierChain;

        $this->assertCount(2, $chain->modifierChain);

        $modifierOne = $chain->modifierChain[0];
        $modifierTwo = $chain->modifierChain[1];

        $this->assertModifierName('upper', $modifierOne);
        $this->assertModifierName('lower', $modifierTwo);

        $this->assertCount(3, $modifierOne->valueNodes);

        /** @var ModifierValueNode $valueOne */
        $valueOne = $modifierOne->valueNodes[0];
        $this->assertInstanceOf(ModifierValueNode::class, $valueOne);
        $this->assertSame('test', $valueOne->value);

        /** @var ModifierValueNode $valueTwo */
        $valueTwo = $modifierOne->valueNodes[1];
        $this->assertInstanceOf(ModifierValueNode::class, $valueTwo);
        $this->assertSame('param', $valueTwo->value);

        /** @var StringValueNode $valueThree */
        $valueThree = $modifierOne->valueNodes[2];
        $this->assertInstanceOf(StringValueNode::class, $valueThree);
        $this->assertSame('hello :|', $valueThree->value);
    }
}
