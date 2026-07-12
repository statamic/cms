<?php

namespace Tests\Antlers\Parser;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ConditionNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ExecutionBranch;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\EqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalOrOperator;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroup;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Tests\Antlers\ParserTestCase;

class ConditionalNodesTest extends ParserTestCase
{
    public function test_it_doesnt_skip_surrounding_nodes()
    {
        $nodes = $this->parseTemplate('conditions-literals');
        $this->assertCount(3, $nodes);
        $this->assertLiteralNodeContains($nodes[0], 'Outer Start');
        $this->assertLiteralNodeContains($nodes[2], 'Outer end');
        $this->assertIsAntlersNode($nodes[1]);

        /** @var AntlersNode $articlesNode */
        $articlesNode = $nodes[1];
        $this->assertSame(' articles ', $articlesNode->content);
        $this->assertCount(4, $articlesNode->children);

        $children = $articlesNode->children;

        $this->assertLiteralNodeContains($children[0], 'start');
        $this->assertLiteralNodeContains($children[2], 'end');

        $this->assertIsCondition($children[1]);

        /** @var ConditionNode $condition */
        $condition = $children[1];

        $this->assertCount(3, $condition->logicBranches);
        $this->assertCount(3, $condition->chain);
        $this->assertSame($articlesNode, $condition->parent);
        $this->assertNotNull($condition->startPosition);
        $this->assertNotNull($condition->endPosition);

        $logicBranches = $condition->logicBranches;

        $this->assertInstanceOf(ExecutionBranch::class, $logicBranches[0]);
        $this->assertInstanceOf(ExecutionBranch::class, $logicBranches[1]);
        $this->assertInstanceOf(ExecutionBranch::class, $logicBranches[2]);

        $firstBranch = $logicBranches[0];
        $this->assertCount(2, $firstBranch->nodes);
        $this->assertLiteralNodeContains($firstBranch->nodes[0], 'Inner literal one.');

        $secondBranch = $logicBranches[1];
        $this->assertLiteralNodeContains($secondBranch->nodes[0], 'Inner literal two.');

        $thirdBranch = $logicBranches[2];
        $this->assertLiteralNodeContains($thirdBranch->nodes[0], 'Else- inner literal three.');
    }

    public function test_nested_conditionals_and_chains_are_parsed()
    {
        $nodes = $this->parseTemplate('nested-conditionals');
        $this->assertCount(3, $nodes);
        $this->assertLiteralNodeContains($nodes[0], 'Outer Start');
        $this->assertLiteralNodeContains($nodes[2], 'Outer end');

        $this->assertIsAntlersNode($nodes[1]);

        /** @var AntlersNode $antlersNode */
        $antlersNode = $nodes[1];
        $this->assertStringContainsString('articles', $antlersNode->content);
        $this->assertCount(4, $antlersNode->children);

        $this->assertLiteralNodeContains($antlersNode->children[0], 'start');
        $this->assertLiteralNodeContains($antlersNode->children[2], 'end');
        $this->assertIsCondition($antlersNode->children[1]);

        /** @var ConditionNode $condNode */
        $condNode = $antlersNode->children[1];
        $this->assertCount(3, $condNode->logicBranches);

        $this->assertConditionalChainContainsSteps($condNode, [
            "if title == 'Nectar of the Gods'",
            'elseif 5 < 10',
            'else',
        ]);

        $this->assertCount(4, $condNode->logicBranches[0]->head->children);
        $this->assertCount(2, $condNode->logicBranches[1]->head->children);
        $this->assertCount(2, $condNode->logicBranches[2]->head->children);

        /** @var ExecutionBranch $firstBranch */
        $firstBranch = $condNode->logicBranches[0];
        $this->assertLiteralNodeContains($firstBranch->nodes[0], 'Inner literal one.');
        $this->assertInstanceOf(LiteralNode::class, $firstBranch->nodes[2]);

        /** @var ConditionNode $nestedCond1 */
        $nestedCond1 = $firstBranch->nodes[1];

        $this->assertConditionalChainContainsSteps($nestedCond1, [
            'if true == true',
            'else',
        ]);

        $this->assertCount(4, $nestedCond1->logicBranches[0]->head->children);
        $this->assertCount(2, $nestedCond1->logicBranches[1]->head->children);

        $nestedCond2 = $nestedCond1->logicBranches[0]->head->children[1];

        $this->assertConditionalChainContainsSteps($nestedCond2, [
            'if true == false',
            'elseif false == true',
        ]);

        $nestedCond3 = $nestedCond1->logicBranches[0]->head->children[1]->logicBranches[1]->head->children[1];
        $this->assertConditionalChainContainsSteps($nestedCond3, [
            "if abc == 'abc'",
        ]);
    }

    public function test_unless_rewrite_sets_content()
    {
        $template = <<<'EOT'
{{ unless true == false }}

{{ /unless }}
EOT;

        /** @var AntlersNode $ifNode */
        $ifNode = $this->parseNodes($template)[0];
        $this->assertGreaterThan(0, count($ifNode->runtimeNodes));
        $this->assertSame(' !( true == false ) ', $ifNode->getContent());
    }

    public function test_conditions_do_not_get_parsed_as_modifiers()
    {
        $template = <<<'EOT'
{{ if is_small_article || collection:handle == 'vacancies' }}{{ /if }}
EOT;

        $nodes = $this->parseNodes($template);
        $this->assertCount(2, $nodes);
        $this->assertInstanceOf(AntlersNode::class, $nodes[0]);
        $this->assertInstanceOf(AntlersNode::class, $nodes[1]);

        /** @var AntlersNode $firstNode */
        $firstNode = $nodes[0];
        // The () are added automatically.
        $this->assertCount(9, $firstNode->runtimeNodes);

        // Parse the text that would produce the 7 runtime nodes above.
        $runtimeNodes = $this->getParsedRuntimeNodes("{{ is_small_article || collection:handle == 'vacancies' }}");
        //                                SG       LG
        $runtimeNodes = $runtimeNodes[0]->nodes[0]->nodes;

        $this->assertInstanceOf(VariableNode::class, $runtimeNodes[0]);
        $this->assertSame('is_small_article', $runtimeNodes[0]->name);
        $this->assertInstanceOf(LogicalOrOperator::class, $runtimeNodes[1]);
        $this->assertInstanceOf(LogicGroup::class, $runtimeNodes[2]);

        // Unwrap the logic group representing collection:handle == 'vacancies'
        $runtimeNodes = $runtimeNodes[2]->nodes;

        $this->assertInstanceOf(VariableNode::class, $runtimeNodes[0]);
        $this->assertInstanceOf(EqualCompOperator::class, $runtimeNodes[1]);
        $this->assertInstanceOf(StringValueNode::class, $runtimeNodes[2]);

        $this->assertSame('collection:handle', $runtimeNodes[0]->name);
        $this->assertSame('vacancies', $runtimeNodes[2]->value);
    }
}
