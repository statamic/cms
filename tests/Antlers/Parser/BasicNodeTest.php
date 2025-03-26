<?php

namespace Tests\Antlers\Parser;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Constants\FalseConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\TrueConstant;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\EqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalAndOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalOrOperator;
use Statamic\View\Antlers\Language\Nodes\Paths\PathNode;
use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\SemanticGroup;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\Fixtures\Addon\Tags\EchoMethod;
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
        $nodes = $this->parseNodes('{{ meta_title["No Title Set"] param="Test" }}');
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
<div class="max-w-6xl mx-auto mb-32">
<p>test</p> {{ subtitle }}
</div>
EOT;

        $expected = <<<'EOT'

<div class="max-w-6xl mx-auto mb-32">
<p>test</p> test
</div>
EOT;

        $result = $this->renderString($template, ['subtitle' => 'test']);

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $result);
    }

    public function test_nodes_with_length_five_do_not_skip_literals()
    {
        // Note: 5 is the number of characters the document retrieves at a time.
        $template = '    {{a}} end';
        $nodes = $this->parseNodes($template);

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);
        $this->assertInstanceOf(AntlersNode::class, $nodes[1]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[2]);

        /** @var LiteralNode $firstLiteral */
        $firstLiteral = $nodes[0];
        $this->assertSame('    ', $firstLiteral->content);

        /** @var AntlersNode $antlersNode */
        $antlersNode = $nodes[1];
        $this->assertSame('a', $antlersNode->content);

        /** @var LiteralNode $secondLiteral */
        $secondLiteral = $nodes[2];
        $this->assertSame(' end', $secondLiteral->content);

        $template = <<<'EOT'
<one>{{ a = "A" b = "B" c = "C" d = "D" }}<two>
{{a}}<three>{{b}}<four>{{c}}<five>{{d}}<six>
EOT;

        $expected = <<<'EOT'
<one><two>
A<three>B<four>C<five>D<six>
EOT;

        $this->assertSame($expected, trim($this->renderString($template)));

        EchoMethod::register();

        // All together now.
        $data = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'e' => 'E',
            'g' => 'G',
            'url' => 'https://en.wikipedia.org/wiki/Count_von_Count',
            'method' => 'some_method',
            'title' => 'The Title',
            'articles' => [
                ['title' => 'one'],
                ['title' => 'two'],
                ['title' => 'three'],
            ],
        ];

        // The missing "F" in the output is not an accident.
        $template = <<<'EOT'
<one>     {{ title }}<two>{{a}}<three>
{{echo_method:{{a}}}}<four>{{ echo_method:{{method}}  }}|abc {{b}}{{c}}
{{a}}-{{b}}-{{c}}-{{d}}-{{e}}-{{method}}
{{ articles }}<{{title}}>{{ /articles }}
{{ echo_method:parameter param="{{a}}{{b}}{{c}}{{d}}{{e}}{{f}}{{g}}--just-to-be-sure--{ echo_method:parameter param="{{a}}{{b}}{{c}}{{d}}{{e}}{{f}}{{g}}" }--after" }}<five>{{ articles}}<six>
<title:{{ title }}>{{ echo_method:{{title}}}}{{a}}
     {{ /articles }}<seven>{{ url }}{{ title }}
Just to test ASCII stuff within this madness.{{ my_counter = 0; }}
{{ articles }}{{ my_counter += 1; }}
<{{my_counter}}:{{ another_var = 'aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz' + '<{title}>'; another_var }}>
{{ /articles }}
{{ a = "a"; b = "b"; c = "c"; d = "d";
   e = "e"; f = "f"; g = "g"; h = "h";
   i = "i"; j = "j"; k = "k"; l = "l";
   m = "m"; n = "n"; o = "o"; p = "p";
   q = "q"; r = "r"; s = "s"; t = "t";
   u = "u"; v = "v"; w = "w"; x = "x";
   y = "y"; z = "z";

   A = "A"; B = "B"; C = "C"; D = "D";
   E = "E"; F = "F"; G = "G"; H = "H";
   I = "I"; J = "J"; K = "K"; L = "L";
   M = "M"; N = "N"; O = "O"; P = "P";
   Q = "Q"; R = "R"; S = "S"; T = "T";
   U = "U"; V = "V"; W = "W"; X = "X";
   Y = "Y"; Z = "Z";
}}
{{# Comments {{articles}}{{ title }}{{ /articles}} #}}
{{ echo_method:parameter param="{a}{b}{c}{d}{e}{f}{g}{h}{i}{j}{k}{l}{m}{n}{o}{p}{q}{r}{s}{t}{u}{v}{w}{x}{y}{z}" }}
{{ echo_method:parameter param="{A}{B}{C}{D}{E}{F}{G}{H}{I}{J}{K}{L}{M}{N}{O}{P}{Q}{R}{S}{T}{U}{V}{W}{X}{Y}{Z}" }}
{{ result = '🥳🥳' }}-{{a}}-{{b}}-{{c}}{{ result }} <end>

<start-noparse>
{{ noparse }}
<one>     {{ title }}<two>{{a}}<three>
{{echo_method:{{a}}}}<four>{{ echo_method:{{method}}  }}|abc {{b}}{{c}}
{{a}}-{{b}}-{{c}}-{{d}}-{{e}}-{{method}}
{{ articles }}<{{title}}>{{ /articles }}
{{ echo_method:parameter param="{{a}}{{b}}{{c}}{{d}}{{e}}{{f}}{{g}}--just-to-be-sure--{ echo_method:parameter param="{{a}}{{b}}{{c}}{{d}}{{e}}{{f}}{{g}}" }--after" }}<five>{{ articles}}<six>
<title:{{ title }}>{{ echo_method:{{title}}}}{{a}}
     {{ /articles }}<seven>{{ url }}{{ title }}
Just to test ASCII stuff within this madness.{{ my_counter = 0; }}
{{ articles }}{{ my_counter += 1; }}
<{{my_counter}}:{{ another_var = 'aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz' + '<{title}>'; another_var }}>
{{ /articles }}
{{ a = "a"; b = "b"; c = "c"; d = "d";
   e = "e"; f = "f"; g = "g"; h = "h";
   i = "i"; j = "j"; k = "k"; l = "l";
   m = "m"; n = "n"; o = "o"; p = "p";
   q = "q"; r = "r"; s = "s"; t = "t";
   u = "u"; v = "v"; w = "w"; x = "x";
   y = "y"; z = "z";

   A = "A"; B = "B"; C = "C"; D = "D";
   E = "E"; F = "F"; G = "G"; H = "H";
   I = "I"; J = "J"; K = "K"; L = "L";
   M = "M"; N = "N"; O = "O"; P = "P";
   Q = "Q"; R = "R"; S = "S"; T = "T";
   U = "U"; V = "V"; W = "W"; X = "X";
   Y = "Y"; Z = "Z";
}}
{{# Comments {{articles}}{{ title }}{{ /articles}} #}}
{{ echo_method:parameter param="{a}{b}{c}{d}{e}{f}{g}{h}{i}{j}{k}{l}{m}{n}{o}{p}{q}{r}{s}{t}{u}{v}{w}{x}{y}{z}" }}
{{ echo_method:parameter param="{A}{B}{C}{D}{E}{F}{G}{H}{I}{J}{K}{L}{M}{N}{O}{P}{Q}{R}{S}{T}{U}{V}{W}{X}{Y}{Z}" }}
{{ result = '🥳🥳' }}-{{a}}-{{b}}-{{c}}{{ result }} <end>
{{ /noparse }}
<end-noparse>

and again
{{# Rest values to initial. #}}
{{ a = 'A' b = 'B' c = 'C' d = 'D' e = 'E' f = null g = 'G' }}
<one>     {{ title }}<two>{{a}}<three>
{{echo_method:{{a}}}}<four>{{ echo_method:{{method}}  }}|abc {{b}}{{c}}
{{a}}-{{b}}-{{c}}-{{d}}-{{e}}-{{method}}
{{ articles }}<{{title}}>{{ /articles }}
{{ echo_method:parameter param="{{a}}{{b}}{{c}}{{d}}{{e}}{{f}}{{g}}--just-to-be-sure--{ echo_method:parameter param="{{a}}{{b}}{{c}}{{d}}{{e}}{{f}}{{g}}" }--after" }}<five>{{ articles}}<six>
<title:{{ title }}>{{ echo_method:{{title}}}}{{a}}
     {{ /articles }}<seven>{{ url }}{{ title }}
Just to test ASCII stuff within this madness.{{ my_counter = 0; }}
{{ articles }}{{ my_counter += 1; }}
<{{my_counter}}:{{ another_var = 'aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz' + '<{title}>'; another_var }}>
{{ /articles }}
{{ a = "a"; b = "b"; c = "c"; d = "d";
   e = "e"; f = "f"; g = "g"; h = "h";
   i = "i"; j = "j"; k = "k"; l = "l";
   m = "m"; n = "n"; o = "o"; p = "p";
   q = "q"; r = "r"; s = "s"; t = "t";
   u = "u"; v = "v"; w = "w"; x = "x";
   y = "y"; z = "z";

   A = "A"; B = "B"; C = "C"; D = "D";
   E = "E"; F = "F"; G = "G"; H = "H";
   I = "I"; J = "J"; K = "K"; L = "L";
   M = "M"; N = "N"; O = "O"; P = "P";
   Q = "Q"; R = "R"; S = "S"; T = "T";
   U = "U"; V = "V"; W = "W"; X = "X";
   Y = "Y"; Z = "Z";
}}
{{# Comments {{articles}}{{ title }}{{ /articles}} #}}
{{ echo_method:parameter param="{a}{b}{c}{d}{e}{f}{g}{h}{i}{j}{k}{l}{m}{n}{o}{p}{q}{r}{s}{t}{u}{v}{w}{x}{y}{z}" }}
{{ echo_method:parameter param="{A}{B}{C}{D}{E}{F}{G}{H}{I}{J}{K}{L}{M}{N}{O}{P}{Q}{R}{S}{T}{U}{V}{W}{X}{Y}{Z}" }}
{{ result = '🥳🥳' }}-{{a}}-{{b}}-{{c}}{{ result }} <end>{{noparse}}{{a}}{{/noparse}}<the-end>
EOT;

        $expected = <<<'EOT'
<one>     The Title<two>A<three>
A<four>some_method|abc BC
A-B-C-D-E-some_method
<one><two><three>
ABCDEG--just-to-be-sure--ABCDEG--after<five><six>
<title:one>oneA
     <six>
<title:two>twoA
     <six>
<title:three>threeA
     <seven>https://en.wikipedia.org/wiki/Count_von_CountThe Title
Just to test ASCII stuff within this madness.

<1:aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz<one>>

<2:aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz<two>>

<3:aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz<three>>



abcdefghijklmnopqrstuvwxyz
ABCDEFGHIJKLMNOPQRSTUVWXYZ
-a-b-c🥳🥳 <end>

<start-noparse>

<one>     {{ title }}<two>{{a}}<three>
{{echo_method:{{a}}}}<four>{{ echo_method:{{method}}  }}|abc {{b}}{{c}}
{{a}}-{{b}}-{{c}}-{{d}}-{{e}}-{{method}}
{{ articles }}<{{title}}>{{ /articles }}
{{ echo_method:parameter param="{{a}}{{b}}{{c}}{{d}}{{e}}{{f}}{{g}}--just-to-be-sure--{ echo_method:parameter param="{{a}}{{b}}{{c}}{{d}}{{e}}{{f}}{{g}}" }--after" }}<five>{{ articles}}<six>
<title:{{ title }}>{{ echo_method:{{title}}}}{{a}}
     {{ /articles }}<seven>{{ url }}{{ title }}
Just to test ASCII stuff within this madness.{{ my_counter = 0; }}
{{ articles }}{{ my_counter += 1; }}
<{{my_counter}}:{{ another_var = 'aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz' + '<{title}>'; another_var }}>
{{ /articles }}
{{ a = "a"; b = "b"; c = "c"; d = "d";
   e = "e"; f = "f"; g = "g"; h = "h";
   i = "i"; j = "j"; k = "k"; l = "l";
   m = "m"; n = "n"; o = "o"; p = "p";
   q = "q"; r = "r"; s = "s"; t = "t";
   u = "u"; v = "v"; w = "w"; x = "x";
   y = "y"; z = "z";

   A = "A"; B = "B"; C = "C"; D = "D";
   E = "E"; F = "F"; G = "G"; H = "H";
   I = "I"; J = "J"; K = "K"; L = "L";
   M = "M"; N = "N"; O = "O"; P = "P";
   Q = "Q"; R = "R"; S = "S"; T = "T";
   U = "U"; V = "V"; W = "W"; X = "X";
   Y = "Y"; Z = "Z";
}}
{{# Comments {{articles}}{{ title }}{{ /articles}} #}}
{{ echo_method:parameter param="{a}{b}{c}{d}{e}{f}{g}{h}{i}{j}{k}{l}{m}{n}{o}{p}{q}{r}{s}{t}{u}{v}{w}{x}{y}{z}" }}
{{ echo_method:parameter param="{A}{B}{C}{D}{E}{F}{G}{H}{I}{J}{K}{L}{M}{N}{O}{P}{Q}{R}{S}{T}{U}{V}{W}{X}{Y}{Z}" }}
{{ result = '🥳🥳' }}-{{a}}-{{b}}-{{c}}{{ result }} <end>

<end-noparse>

and again


<one>     The Title<two>A<three>
A<four>some_method|abc BC
A-B-C-D-E-some_method
<one><two><three>
ABCDEG--just-to-be-sure--ABCDEG--after<five><six>
<title:one>oneA
     <six>
<title:two>twoA
     <six>
<title:three>threeA
     <seven>https://en.wikipedia.org/wiki/Count_von_CountThe Title
Just to test ASCII stuff within this madness.

<1:aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz<one>>

<2:aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz<two>>

<3:aaa ’“”•–—˜™š›œ žŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿzzz<three>>



abcdefghijklmnopqrstuvwxyz
ABCDEFGHIJKLMNOPQRSTUVWXYZ
-a-b-c🥳🥳 <end>{{a}}<the-end>
EOT;

        $this->assertSame($expected, $this->renderString($template, $data, true));
    }

    public function test_variable_nodes_are_combined_neighboring_array_accessors()
    {
        $nodes = $this->getParsedRuntimeNodes('{{ posts[1]title }}');

        $this->assertInstanceOf(SemanticGroup::class, $nodes[0]);

        /** @var SemanticGroup $wrapperGroup */
        $wrapperGroup = $nodes[0];
        $this->assertCount(1, $wrapperGroup->nodes);

        $this->assertInstanceOf(VariableNode::class, $wrapperGroup->nodes[0]);

        /** @var VariableNode $variableNode */
        $variableNode = $wrapperGroup->nodes[0];

        $this->assertSame('posts[1]title', $variableNode->name);
        $this->assertNotNull($variableNode->variableReference);

        $this->assertCount(3, $variableNode->variableReference->pathParts);

        $pathNode = $variableNode->variableReference->pathParts[0];
        $this->assertInstanceOf(PathNode::class, $pathNode);
        $this->assertSame('posts', $pathNode->name);

        $varRef = $variableNode->variableReference->pathParts[1];
        $this->assertInstanceOf(VariableReference::class, $varRef);
        $this->assertCount(1, $varRef->pathParts);
        $this->assertInstanceOf(PathNode::class, $varRef->pathParts[0]);

        /** @var PathNode $innerPathNode */
        $innerPathNode = $varRef->pathParts[0];
        $this->assertSame('1', $innerPathNode->name);

        $pathNode2 = $variableNode->variableReference->pathParts[2];
        $this->assertInstanceOf(PathNode::class, $pathNode2);
        $this->assertSame('title', $pathNode2->name);
    }

    public function test_uppercase_logical_keywords_are_parsed_into_keywords_and_not_variables()
    {
        $nodes = $this->getParsedRuntimeNodes('{{ TrUe AND FALSE oR something }}');
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(SemanticGroup::class, $nodes[0]);

        /** @var SemanticGroup $semanticGroupWrapper */
        $semanticGroupWrapper = $nodes[0];
        $this->assertCount(1, $semanticGroupWrapper->nodes);
        $this->assertInstanceOf(LogicGroup::class, $semanticGroupWrapper->nodes[0]);

        /** @var LogicGroup $logicWrapper */
        $logicWrapper = $semanticGroupWrapper->nodes[0];
        $this->assertCount(3, $logicWrapper->nodes);

        $this->assertInstanceOf(LogicGroup::class, $logicWrapper->nodes[0]);
        $this->assertInstanceOf(LogicalOrOperator::class, $logicWrapper->nodes[1]);
        $this->assertInstanceOf(VariableNode::class, $logicWrapper->nodes[2]);

        /** @var LogicGroup $innerGroup */
        $innerGroup = $logicWrapper->nodes[0];
        $this->assertCount(3, $innerGroup->nodes);
        $this->assertInstanceOf(TrueConstant::class, $innerGroup->nodes[0]);
        $this->assertInstanceOf(LogicalAndOperator::class, $innerGroup->nodes[1]);
        $this->assertInstanceOf(FalseConstant::class, $innerGroup->nodes[2]);

        /** @var VariableNode $var */
        $var = $logicWrapper->nodes[2];
        $this->assertSame('something', $var->name);
    }
}
