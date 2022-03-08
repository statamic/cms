<?php

namespace Tests\Antlers\Parser;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\EqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Paths\PathNode;
use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroup;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class BasicNodeTest extends ParserTestCase
{
    public function test_it_returns_nodes()
    {
        $nodes = $this->parseNodes('{{ hello }}, World {{ third }}.');

        $this->assertSame(4, count($nodes));
        $this->assertInstanceOf(AntlersNode::class, $nodes[0]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[1]);
        $this->assertInstanceOf(AntlersNode::class, $nodes[2]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[3]);
    }

    public function test_it_doesnt_trim_off_content_start()
    {
        $nodes = $this->parseNodes('{{ meta_title ?? title ?? "No Title Set" }}');

        $this->assertSame(' meta_title ?? title ?? "No Title Set" ', $nodes[0]->getContent());
    }

    public function test_it_removes_params_from_node_content()
    {
        $nodes = $this->parseNodes('{{ meta_title["No Title Set"] ?? title param="Test" }}');

        $this->assertSame(' meta_title["No Title Set"] ?? title ', $nodes[0]->getContent());
    }

    public function test_node_name_ignores_modifier_start()
    {
        /** @var AntlersNode $node */
        $node = $this->parseNodes('{{ form:handle|upper }}')[0];
        $this->assertInstanceOf(AntlersNode::class, $node);

        $this->assertSame('form:handle', $node->name->content);
    }

    public function test_it_removes_tags_from_node_content()
    {
        $nodes = $this->parseNodes('{{ meta_title["No Title Set"] param="Test" }}{{ /if }}');
        $this->assertSame(' meta_title["No Title Set"] ', $nodes[0]->getContent());
    }

    public function test_it_parses_simple_comments()
    {
        $nodes = $this->parseNodes('{{# {{ collection:count from="articles" }} #}}');
        $this->assertCount(1, $nodes);
        $this->assertTrue($nodes[0]->isComment);
        $this->assertSame(' {{ collection:count from="articles" }} ', $nodes[0]->getContent());
    }

    public function test_it_parses_full_variable_names()
    {
        $result = $this->getParsedRuntimeNodes('{{ view:test[hello] | upper:test:param:"hello :|" | lower }}')[0];
        /** @var VariableNode $firstNode */
        $firstNode = $result->nodes[0];
        $this->assertSame('view:test[hello]', $firstNode->name);
        $this->assertCount(2, $firstNode->modifierChain->modifierChain);

        $result = $this->getParsedRuntimeNodes('{{ view:test[view:test[nested:data[more:nested[keys]]]]|upper:test:param:"hello :|"|lower }}')[0];
        /** @var VariableNode $firstNode */
        $firstNode = $result->nodes[0];
        $this->assertSame('view:test[view:test[nested:data[more:nested[keys]]]]', $firstNode->name);
        $this->assertCount(2, $firstNode->modifierChain->modifierChain);
    }

    public function test_it_parses_embedded_dot_paths()
    {
        $input = '{{ view:data:test[nested.key[path:path1]] | upper | lower }}';
        /** @var VariableNode $result */
        $result = $this->getParsedRuntimeNodes($input)[0]->nodes[0];

        $this->assertSame('view:data:test[nested.key[path:path1]]', $result->name);
    }

    public function test_it_parses_multiple_variables_separately()
    {
        $input = '{{ (view:test[hello] | upper:test:param:"hello :|" | lower) == (view:title|lower)}}';

        /** @var LogicGroup $result */
        $result = $this->getParsedRuntimeNodes($input)[0]->nodes[0];
        $result = $result->nodes;

        $this->assertInstanceOf(LogicGroup::class, $result[0]);
        $this->assertInstanceOf(EqualCompOperator::class, $result[1]);
        $this->assertInstanceOf(LogicGroup::class, $result[2]);

        //                      N:LG      N:SG
        $firstVar = $result[0]->nodes[0]->nodes[0];
        $this->assertSame('view:test[hello]', $firstVar->name);

        //                      N:LG      N:SG
        $secondVar = $result[2]->nodes[0]->nodes[0];
        $this->assertSame('view:title', $secondVar->name);

        $input = '{{(view:test[hello]|upper:test:param:"hello :|"|lower)==(view:title|lower)}}';
        // Outer will be a semantic group.
        $result = $this->getParsedRuntimeNodes($input)[0]->nodes[0];
        $result = $result->nodes;

        $this->assertInstanceOf(LogicGroup::class, $result[0]);
        $this->assertInstanceOf(EqualCompOperator::class, $result[1]);
        $this->assertInstanceOf(LogicGroup::class, $result[2]);

        //                      N:LG      N:SG
        $firstVar = $result[0]->nodes[0]->nodes[0];
        $this->assertSame('view:test[hello]', $firstVar->name);

        //                      N:LG      N:SG
        $secondVar = $result[2]->nodes[0]->nodes[0];
        $this->assertSame('view:title', $secondVar->name);
    }

    public function test_complex_variable_reference_paths_are_parsed_within_variable_nodes()
    {
        $input = '{{ view:data:test[nested.key[path:path1]]|upper|lower }}';
        /** @var VariableNode $variable */
        $variable = $this->getParsedRuntimeNodes($input)[0]->nodes[0];

        $this->assertInstanceOf(VariableNode::class, $variable);
        $this->assertNotNull($variable->variableReference);

        $result = $variable->variableReference;
        $part1 = $result->pathParts[0];
        $part2 = $result->pathParts[1];
        $part3 = $result->pathParts[2];

        /** @var VariableReference $part4 */
        $part4 = $result->pathParts[3];

        $this->assertInstanceOf(PathNode::class, $part1);
        $this->assertInstanceOf(PathNode::class, $part2);
        $this->assertInstanceOf(PathNode::class, $part3);
        $this->assertInstanceOf(VariableReference::class, $part4);

        $this->assertSame('view', $part1->name);
        $this->assertSame('data', $part2->name);
        $this->assertSame('test', $part3->name);

        $this->assertCount(3, $part4->pathParts);

        $nestedPart1 = $part4->pathParts[0];
        $nestedPart2 = $part4->pathParts[1];
        $nestedPart3 = $part4->pathParts[2];

        $this->assertInstanceOf(PathNode::class, $nestedPart1);
        $this->assertInstanceOf(PathNode::class, $nestedPart2);
        $this->assertInstanceOf(VariableReference::class, $nestedPart3);

        $this->assertSame('nested', $nestedPart1->name);
        $this->assertSame('key', $nestedPart2->name);

        $this->assertCount(2, $nestedPart3->pathParts);

        $subNestedPart1 = $nestedPart3->pathParts[0];
        $subNestedPart2 = $nestedPart3->pathParts[1];

        $this->assertInstanceOf(PathNode::class, $subNestedPart1);
        $this->assertInstanceOf(PathNode::class, $subNestedPart2);

        $this->assertSame('path', $subNestedPart1->name);
        $this->assertSame('path1', $subNestedPart2->name);
    }

    public function test_comments_with_things_that_look_like_antlers_dont_skip_literal_nodes()
    {
        $input = '{{# test comment {{ var }} #}}<p>I am a literal.</p>';
        $nodes = $this->parseNodes($input);

        $this->assertCount(2, $nodes);

        $this->assertInstanceOf(AntlersNode::class, $nodes[0]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[1]);

        $this->assertSame(' test comment {{ var }} ', $nodes[0]->getContent());
        $this->assertSame('<p>I am a literal.</p>', $nodes[1]->content);
    }

    public function test_neighboring_comments_dont_confuse_things()
    {
        $template = <<<'EOT'
{{# A comment #}}
{{# another comment {{ width }} #}}
<div class="max-w-2xl mx-auto mb-32">
<p>test</p> {{ subtitle }}
</div>
EOT;

        $expected = <<<'EOT'

<div class="max-w-2xl mx-auto mb-32">
<p>test</p> test
</div>
EOT;

        $result = $this->renderString($template, ['subtitle' => 'test']);

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $result);
    }
}
