<?php

namespace Tests\Antlers\Parser;

use Statamic\View\Antlers\Language\Nodes\Structures\SemanticGroup;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Tests\Antlers\ParserTestCase;

class VariableParsingTest extends ParserTestCase
{

    public function test_variables_can_use_hyphens()
    {
        $nodes = $this->getParsedRuntimeNodes('{{ the-variable-name }}');

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(SemanticGroup::class, $nodes[0]);
        $this->assertCount(1, $nodes[0]->nodes);
        $this->assertInstanceOf(VariableNode::class, $nodes[0]->nodes[0]);

        /** @var VariableNode $variable */
        $variable = $nodes[0]->nodes[0];
        $this->assertSame('the-variable-name', $variable->name);

        $nodes = $this->getParsedRuntimeNodes('{{ the-variable }}');

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(SemanticGroup::class, $nodes[0]);
        $this->assertCount(1, $nodes[0]->nodes);
        $this->assertInstanceOf(VariableNode::class, $nodes[0]->nodes[0]);

        /** @var VariableNode $variable */
        $variable = $nodes[0]->nodes[0];
        $this->assertSame('the-variable', $variable->name);
    }
}