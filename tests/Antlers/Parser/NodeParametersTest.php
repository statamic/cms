<?php

namespace Tests\Antlers\Parser;

use Carbon\Carbon;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Nodes\Parameters\ParameterNode;
use Tests\Antlers\ParserTestCase;

class NodeParametersTest extends ParserTestCase
{
    /**
     * @param  ParameterNode  $parameter
     * @param  string  $name
     * @param  string  $value
     */
    protected function assertParameterNameValue($parameter, $name, $value)
    {
        $this->assertSame($name, $parameter->name);
        $this->assertSame($value, $parameter->value);
    }

    public function test_at_params_can_be_supplied()
    {
        $template = <<<'EOT'
{{ form:create test="test-value" attr:@submit.prevent="sendForm()" }}
EOT;

        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(AntlersNode::class, $nodes[0]);

        /** @var AntlersNode $node */
        $node = $nodes[0];

        $this->assertCount(2, $node->parameters);

        $param1 = $node->parameters[0];
        $param2 = $node->parameters[1];

        $this->assertSame('test', $param1->name);
        $this->assertSame('test-value', $param1->value);

        $this->assertSame('attr:@submit.prevent', $param2->name);
        $this->assertSame('sendForm()', $param2->value);
    }

    public function test_tag_parameters_can_start_with_numbers()
    {
        $template = <<<'EOT'
{{ tag lg:ratio="2" 2xl:ratio="7" }}
EOT;

        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(AntlersNode::class, $nodes[0]);

        /** @var AntlersNode $node */
        $node = $nodes[0];

        $this->assertCount(2, $node->parameters);

        $param1 = $node->parameters[0];
        $param2 = $node->parameters[1];

        $this->assertSame('lg:ratio', $param1->name);
        $this->assertSame('2', $param1->value);

        $this->assertSame('2xl:ratio', $param2->name);
        $this->assertSame('7', $param2->value);
    }

    public function test_node_parameter_escape_consistent_behavior()
    {
        // Ensure that the escape sequence behavior
        // of the runtime is backwards compatible.
        $dateString = '2020-09-23T19:51:39+00:00';

        $date = Carbon::parse($dateString);

        $result = $this->renderString('{{ date format="Y-m-d\\TH:i:sP" }}', [
            'date' => $date,
        ], true);

        $this->assertSame($dateString, $result);
    }

    public function test_node_parameter_paired_behavior()
    {
        /** @var AntlersNode $unPairedNode */
        $unPairedNode = $this->parseNodes('{{ date format="H|i|s" }}')[0];

        /** @var AntlersNode $pairedNode */
        $pairedNode = $this->parseNodes('{{ date format="H|i|s" }}{{ /date }}')[0];

        $this->assertCount(3, $unPairedNode->getModifierParameterValues([]));
        $this->assertCount(3, $pairedNode->getModifierParameterValues([]));
    }

    public function test_pipe_can_be_escaped_inside_modifier_parameters()
    {
        /** @var AntlersNode $node */
        $node = $this->parseNodes('{{ date format="param\|one|param_two|param_three|param\|\|four|||final" }}')[0];

        $parameters = $node->getModifierParameterValues([]);

        $this->assertCount(7, $parameters);
        $this->assertSame([
            'param|one',
            'param_two',
            'param_three',
            'param||four',
            '',
            '',
            'final',
        ], $parameters);
    }

    public function test_parameter_details_are_parsed()
    {
        /** @var AntlersNode $node */
        $node = $this->parseNodes('{{ identifier :parameter="value" param="value-two" }}')[0];

        $this->assertCount(2, $node->parameters);
        $this->assertParameterNameValue($node->parameters[0], 'parameter', 'value');
        $this->assertParameterNameValue($node->parameters[1], 'param', 'value-two');
    }

    public function test_variable_references_are_parsed()
    {
        /** @var AntlersNode $node */
        $node = $this->parseNodes('{{ identifier :parameter="value" }}')[0];

        $this->assertCount(1, $node->parameters);
        $this->assertTrue($node->parameters[0]->isVariableReference);
    }

    public function test_it_detects_modifier_parameters()
    {
        /** @var AntlersNode $node */
        $node = $this->parseNodes('{{ identifier :parameter="value" param="value-two" lower="true" }}')[0];

        $this->assertCount(3, $node->parameters);
        $this->assertParameterNameValue($node->parameters[0], 'parameter', 'value');
        $this->assertFalse($node->parameters[0]->isModifierParameter);
        $this->assertParameterNameValue($node->parameters[1], 'param', 'value-two');
        $this->assertFalse($node->parameters[1]->isModifierParameter);
        $this->assertParameterNameValue($node->parameters[2], 'lower', 'true');
        $this->assertTrue($node->parameters[2]->isModifierParameter);
    }

    public function test_equals_followed_by_space_is_not_parameter()
    {
        /** @var AntlersNode $node */
        $node = $this->parseNodes("{{ is_current || is_parent ?= 'font-medium text-gray-900' }}")[0];

        $this->assertCount(0, $node->parameters);
        $this->assertSame(" is_current || is_parent ?= 'font-medium text-gray-900' ", $node->getContent());
    }

    public function test_equals_followed_by_invalid_char_is_not_parameter()
    {
        /** @var AntlersNode $node */
        $node = $this->parseNodes("{{ title=== 'true' }}")[0];
        $this->assertCount(0, $node->parameters);
    }

    public function test_node_parameter_values_are_resolved_correctly()
    {
        $template = '{{ content strip_tags="p|img|span" safe_truncate="75" ensure_right="..." }}';

        /** @var AntlersNode $node */
        $node = $this->parseNodes($template)[0];

        $this->assertCount(3, $node->parameters);
        $param1 = $node->parameters[0];
        $param2 = $node->parameters[1];
        $param3 = $node->parameters[2];

        $paramValue1 = $node->getModifierParameterValuesForParameter($param1, []);
        $paramValue2 = $node->getModifierParameterValuesForParameter($param2, []);
        $paramValue3 = $node->getModifierParameterValuesForParameter($param3, []);

        $this->assertEquals(['p', 'img', 'span'], $paramValue1);
        $this->assertEquals([75], $paramValue2);
        $this->assertEquals(['...'], $paramValue3);
    }

    public function test_double_braces_inside_a_parameter()
    {
        $template = <<<'EOT'
<figure>
{{ params_tag class="absolute right-0 top-0 w-2/3 md:w-1/3 h-auto opacity-70"
            animation="/visuals/pattern-0{{ count }}.json"
        }}

    <div class="md:absolute md:z-10 p-16 md:p-30 md:bottom-0 md:right-0 w-full md:w-4/5 bg-white/90 md:translate-y-1/3 backdrop-blur-xl backdrop-saturate-150 firefox:bg-white">
        {{ partial:typography/paragraph as="span" :content="title" class="block !mb-8" }}
    </div>
</figure>
EOT;
        $nodes = $this->parseNodes($template);

        $this->assertCount(5, $nodes);
        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);
        $this->assertInstanceOf(AntlersNode::class, $nodes[1]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[2]);
        $this->assertInstanceOf(AntlersNode::class, $nodes[3]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[4]);

        $checkString = '<div class="md:absolute md:z-10 p-16 md:p-30 md:bottom-0 md:right-0 w-full md:w-4/5 bg-white/90 md:translate-y-1/3 backdrop-blur-xl backdrop-saturate-150 firefox:bg-white">';
        $this->assertStringContainsString($checkString, $nodes[2]->content);

        $template = <<<'EOT'
<figure>
{{ params_tag class="absolute right-0 top-0 w-2/3 md:w-1/3 h-auto opacity-70"
            animation="/visuals/pattern-0{{ count }}- {{two}} {{three}four}.json"
        }}

    <div class="md:absolute md:z-10 p-16 md:p-30 md:bottom-0 md:right-0 w-full md:w-4/5 bg-white/90 md:translate-y-1/3 backdrop-blur-xl backdrop-saturate-150 firefox:bg-white">
        {{ partial:typography/paragraph as="span" :content="title" class="block !mb-8" }}
    </div>
</figure>
EOT;
        $nodes = $this->parseNodes($template);

        $this->assertCount(5, $nodes);
        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);
        $this->assertInstanceOf(AntlersNode::class, $nodes[1]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[2]);
        $this->assertInstanceOf(AntlersNode::class, $nodes[3]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[4]);

        $this->assertStringContainsString($checkString, $nodes[2]->content);
    }

    public function test_double_braces_inside_a_parameter_emits_final_literal_node_if_no_other_antlers()
    {
        $template = <<<'EOT'
<figure>{{ params_tag class="absolute right-0 top-0 w-2/3 md:w-1/3 h-auto opacity-70"
            animation="/visuals/pattern-0{{ count }}.json"
        }}FINAL_LITERAL</figure>
EOT;
        $nodes = $this->parseNodes($template);

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);
        $this->assertInstanceOf(AntlersNode::class, $nodes[1]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[2]);
        $this->assertSame('<figure>', $nodes[0]->content);
        $this->assertSame('FINAL_LITERAL</figure>', $nodes[2]->content);

        $template = <<<'EOT'
 <div class="list-of-classes">     {{ partial src="svg/" values="{{ article_icon_color:key }} {{ article_icon_size:key }}" }}FINAL_LITERAL </div>
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);
        $this->assertInstanceOf(AntlersNode::class, $nodes[1]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[2]);

        $this->assertSame(' <div class="list-of-classes">     ', $nodes[0]->content);
        $this->assertSame('FINAL_LITERAL </div>', $nodes[2]->content);
    }

    public function test_shorthand_variable_syntax()
    {
        $template = <<<'EOT'
{{ tag_name :$class }}
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(AntlersNode::class, $nodes[0]);

        $this->assertCount(1, $nodes[0]->parameters);

        $this->assertParameterNameValue($nodes[0]->parameters[0], 'class', 'class');
        $this->assertTrue($nodes[0]->parameters[0]->isVariableReference);
    }

    public function test_multiple_shorthand_variable_parameters()
    {
        $template = <<<'EOT'
{{ tag_name :$class :$another_one }}
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(AntlersNode::class, $nodes[0]);

        $this->assertCount(2, $nodes[0]->parameters);

        $this->assertParameterNameValue($nodes[0]->parameters[0], 'class', 'class');
        $this->assertTrue($nodes[0]->parameters[0]->isVariableReference);

        $this->assertParameterNameValue($nodes[0]->parameters[1], 'another_one', 'another_one');
        $this->assertTrue($nodes[0]->parameters[1]->isVariableReference);
    }

    public function test_it_parses_shorthand_parameters_and_regular_parameters()
    {
        $template = <<<'EOT'
{{ tag_name this="that" :$class cool="beans" :$another_one lucky="egg" }}
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(AntlersNode::class, $nodes[0]);

        $this->assertCount(5, $nodes[0]->parameters);

        $this->assertParameterNameValue($nodes[0]->parameters[0], 'this', 'that');
        $this->assertParameterNameValue($nodes[0]->parameters[1], 'class', 'class');
        $this->assertParameterNameValue($nodes[0]->parameters[2], 'cool', 'beans');
        $this->assertParameterNameValue($nodes[0]->parameters[3], 'another_one', 'another_one');
        $this->assertParameterNameValue($nodes[0]->parameters[4], 'lucky', 'egg');

        $this->assertFalse($nodes[0]->parameters[0]->isVariableReference);
        $this->assertTrue($nodes[0]->parameters[1]->isVariableReference);
        $this->assertFalse($nodes[0]->parameters[2]->isVariableReference);
        $this->assertTrue($nodes[0]->parameters[3]->isVariableReference);
        $this->assertFalse($nodes[0]->parameters[4]->isVariableReference);
    }

    public function test_curly_braces_inside_a_parameter_can_be_ignored_entirely()
    {
        $template = <<<'EOT'
{{ form \x-data="{ open: false }" \attr:x-bind="..." \x-init="() => { open = true }" x-show="open" }}
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(AntlersNode::class, $nodes[0]);

        /** @var AntlersNode $antlersNode */
        $antlersNode = $nodes[0];

        $this->assertTrue($antlersNode->hasParameters);
        $this->assertCount(4, $antlersNode->parameters);

        $pXData = $antlersNode->parameters[0];
        $this->assertSame('x-data', $pXData->name);
        $this->assertSame('\x-data', $pXData->originalName);
        $this->assertSame('{ open: false }', $pXData->value);

        $pXBind = $antlersNode->parameters[1];
        $this->assertSame('attr:x-bind', $pXBind->name);
        $this->assertSame('\attr:x-bind', $pXBind->originalName);
        $this->assertSame('...', $pXBind->value);

        $pXInit = $antlersNode->parameters[2];
        $this->assertSame('x-init', $pXInit->name);
        $this->assertSame('\x-init', $pXInit->originalName);
        $this->assertSame('() => { open = true }', $pXInit->value);

        $pXShow = $antlersNode->parameters[3];
        $this->assertSame('x-show', $pXShow->name);
        $this->assertSame('x-show', $pXShow->originalName);
        $this->assertSame('open', $pXShow->value);
    }
}
