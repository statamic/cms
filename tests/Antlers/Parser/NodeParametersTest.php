<?php

namespace Tests\Antlers\Parser;

use Carbon\Carbon;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
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
        $unPairedNode = $this->parseNodes('{{ date format="H:i:s" }}')[0];

        /** @var AntlersNode $pairedNode */
        $pairedNode = $this->parseNodes('{{ date format="H:i:s" }}{{ /date }}')[0];

        $this->assertCount(1, $unPairedNode->getModifierParameterValues([]));
        $this->assertCount(3, $pairedNode->getModifierParameterValues([]));
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
        $node = $this->parseNodes("{{ is_current || is_parent ?= 'font-medium text-gray-800' }}")[0];

        $this->assertCount(0, $node->parameters);
        $this->assertSame(" is_current || is_parent ?= 'font-medium text-gray-800' ", $node->getContent());
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
}
