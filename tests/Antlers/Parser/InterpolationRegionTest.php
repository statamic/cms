<?php

namespace Tests\Antlers\Parser;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Tests\Antlers\ParserTestCase;

class InterpolationRegionTest extends ParserTestCase
{
    private function assertPosition(string $expected, $node)
    {
        $this->assertSame($expected, sprintf(
            '%d/%d/%d-%d/%d/%d',
            $node->startPosition->offset, $node->startPosition->line, $node->startPosition->char,
            $node->endPosition->offset, $node->endPosition->line, $node->endPosition->char
        ));
    }

    private function interpolation(AntlersNode $node, int $index = 0)
    {
        $regions = array_values($node->processedInterpolationRegions);

        $this->assertCount(1, $regions[$index]);

        return $regions[$index][0];
    }

    public function test_interpolation_positions_follow_document_lines()
    {
        $template = "line one\nline two {{ partial:card title=\"{{ title | upper }}\" }}\nline three {{ tag :x=\"{{ y }}\" }}";
        $nodes = $this->parseNodes($template);

        $this->assertPosition('18/2/10-63/2/55', $nodes[1]);
        $first = $this->interpolation($nodes[1]);
        $this->assertPosition('39/2/31-59/2/51', $first);
        $inner = $this->interpolation($first);
        $this->assertSame(' title | upper ', $inner->content);
        $this->assertPosition('37/2/29-55/2/47', $inner);

        $this->assertPosition('76/3/12-97/3/33', $nodes[3]);
        $second = $this->interpolation($nodes[3]);
        $this->assertPosition('84/3/20-92/3/28', $second);
        $inner = $this->interpolation($second);
        $this->assertSame(' y ', $inner->content);
        $this->assertPosition('84/3/20-90/3/26', $inner);
    }

    public function test_interpolation_sub_parser_keeps_the_neutralized_document_prefix()
    {
        $template = "line one\nline two {{ partial:card title=\"{{ title | upper }}\" }}";
        $nodes = $this->parseNodes($template);
        $interpolation = $this->interpolation($nodes[1]);

        $this->assertSame(
            str_replace('{', '~', mb_substr($template, 0, 39)).'{{{ title | upper }}}',
            $interpolation->getParser()->getParsedContent()
        );
        $this->assertSame('{{{ title | upper }}}', $interpolation->getNodeDocumentText());
    }

    public function test_interpolation_positions_after_multibyte_content()
    {
        $template = "caf\u{00E9}\n\u{65E5}\u{672C} {{ partial:card title=\"{{ title }}\" }}\n\u{1F389} {{ tag :x=\"{{ y | upper }}\" }}";
        $nodes = $this->parseNodes($template);

        $this->assertPosition('8/2/4-45/2/41', $nodes[1]);
        $first = $this->interpolation($nodes[1]);
        $this->assertPosition('27/2/23-39/2/35', $first);
        $this->assertPosition('28/2/24-38/2/34', $this->interpolation($first));

        $this->assertPosition('49/3/3-78/3/32', $nodes[3]);
        $second = $this->interpolation($nodes[3]);
        $this->assertPosition('59/3/13-75/3/29', $second);
        $inner = $this->interpolation($second);
        $this->assertSame(' y | upper ', $inner->content);
        $this->assertPosition('61/3/15-75/3/29', $inner);
    }

    public function test_interpolation_positions_with_crlf_line_endings()
    {
        $nodes = $this->parseNodes("line one\r\nline two {{ partial:card title=\"{{ title }}\" }}");

        $this->assertPosition('18/2/10-55/2/47', $nodes[1]);
        $first = $this->interpolation($nodes[1]);
        $this->assertPosition('37/2/29-49/2/41', $first);
        $this->assertPosition('38/2/30-48/2/40', $this->interpolation($first));
    }

    public function test_multi_line_interpolations_continue_line_numbering()
    {
        $nodes = $this->parseNodes("{{ tag a=\"{{\n  b\n}}\" }}\n{{ c }}");

        $this->assertPosition('0/1/1-22/3/6', $nodes[0]);
        $first = $this->interpolation($nodes[0]);
        $this->assertPosition('6/1/7-16/3/3', $first);
        $inner = $this->interpolation($first);
        $this->assertSame("\n  b\n", $inner->content);
        $this->assertPosition('4/1/5-12/3/2', $inner);
        $this->assertPosition('24/4/1-30/4/7', $nodes[2]);
    }

    public function test_nested_interpolations_keep_their_positions()
    {
        $nodes = $this->parseNodes("a\n{{ tag a=\"{{ b c=\"{{ d }}\" }}\" }}");

        $level1 = $this->interpolation($nodes[1]);
        $this->assertPosition('8/2/7-28/2/27', $level1);
        $level2 = $this->interpolation($level1);
        $this->assertSame(' b c="int_ee2" ', $level2->content);
        $this->assertPosition('6/2/5-24/2/23', $level2);
        $level3 = $this->interpolation($level2);
        $this->assertPosition('14/2/13-22/2/21', $level3);
        $level4 = $this->interpolation($level3);
        $this->assertSame(' d ', $level4->content);
        $this->assertPosition('14/2/13-20/2/19', $level4);
    }

    public function test_nested_interpolations_keep_their_own_line_numbering_under_a_seeded_parser()
    {
        $parser = new DocumentParser();
        $parser->setStartLineSeed(5);
        $parser->parse("a\n\n\n{{ tag a=\"{{ b c=\"{{ d }}\" }}\" }}");
        $nodes = $parser->getNodes();

        $this->assertPosition('4/8/1-36/8/33', $nodes[1]);
        $level1 = $this->interpolation($nodes[1]);
        $this->assertPosition('10/4/7-30/4/27', $level1);
        $level2 = $this->interpolation($level1);
        $this->assertPosition('8/4/5-26/4/23', $level2);
        $level3 = $this->interpolation($level2);
        $this->assertPosition('16/4/13-24/4/21', $level3);
        $level4 = $this->interpolation($level3);
        $this->assertSame(' d ', $level4->content);
        $this->assertPosition('16/4/13-22/4/19', $level4);
    }

    public function test_single_brace_interpolation_at_the_document_start()
    {
        foreach (["{{{abcdefghi}}} \u{00E9}", '{{{abcdefghi}}} e'] as $template) {
            $nodes = $this->parseNodes($template);

            $this->assertSame('int_2b2b4ee', $nodes[0]->content);
            $this->assertPosition('0/1/1-14/1/15', $nodes[0]);
            $interpolation = $this->interpolation($nodes[0]);
            $this->assertSame('abcdefghi', $interpolation->content);
            $this->assertPosition('16/1/17-28/1/29', $interpolation);
            $this->assertInstanceOf(LiteralNode::class, $nodes[1]);
            $this->assertPosition('15/1/16-16/1/17', $nodes[1]);
        }
    }

    public function test_directive_like_text_inside_comments_does_not_break_interpolations()
    {
        foreach ([
            '{{# @props( #}}<div>{{ tag x="{{ y }}" }}</div>',
            '{{ noparse }}@props(<b>{{ /noparse }}<div>{{ tag x="{{ y }}" }}</div>',
        ] as $template) {
            $nodes = $this->parseNodes($template);
            $tag = $nodes[count($nodes) - 2];

            $this->assertSame(' tag x="int_dc5" ', $tag->content);
            $this->assertSame(' y ', $this->interpolation($this->interpolation($tag))->content);
        }
    }

    public function test_an_at_sign_just_before_an_interpolation_escapes_it()
    {
        $nodes = $this->parseNodes('{{ tag @a="{{ v }}" }}');

        $this->assertSame([], array_values($nodes[0]->processedInterpolationRegions)[0]);

        $nodes = $this->parseNodes('{{ tag x="{{ v }}" @a="{{ w }}" }}');
        $this->assertSame(' v ', $this->interpolation($this->interpolation($nodes[0]))->content);
        $this->assertSame(' w ', $this->interpolation($this->interpolation($nodes[0], 1))->content);
    }
}
