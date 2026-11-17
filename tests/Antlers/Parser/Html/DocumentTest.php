<?php

namespace Tests\Antlers\Parser\Html;

use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\View\Antlers\Language\Analyzers\Html\AntlersNode as HtmlAntlersNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Instrumentation\Antlers\ElementInspector;
use Statamic\View\Instrumentation\Antlers\RegionExtents;
use Statamic\View\Instrumentation\HtmlContext;
use Tests\Antlers\ParserTestCase;

class DocumentTest extends ParserTestCase
{
    public function test_antlers_nodes_lazily_resolve_their_parent_elements_and_siblings()
    {
        [$parser, $nodes] = $this->parseHtml('<section><p>before {{ title }} <em>{{ value }}</em> after</p></section>');
        [$title, $value] = $nodes;

        $this->assertSame('p', $title->parentElement()->name());
        $this->assertSame('em', $value->parentElement()->name());
        $this->assertInstanceOf(HtmlAntlersNode::class, $title->htmlNode());
        $this->assertSame($title->htmlNode(), $parser->html()->node($title));
        $this->assertSame($title->htmlNode(), $title->htmlNode());
        $this->assertSame('before ', $title->previousHtmlSibling()->toHtml());
        $this->assertSame(' ', $title->nextHtmlSibling()->toHtml());
    }

    public function test_the_graph_recovers_useful_parents_from_bogus_nesting_and_round_trips_source()
    {
        $template = '<main><p>{{ one }}<div>{{ two }}</bogus><span>{{ three }}</main>';
        [$parser, $nodes] = $this->parseHtml($template);

        $this->assertSame('p', $nodes[0]->parentElement()->name());
        $this->assertSame('div', $nodes[1]->parentElement()->name());
        $this->assertSame('span', $nodes[2]->parentElement()->name());
        $this->assertSame($template, $parser->html()->markDirty()->toHtml());
    }

    #[DataProvider('fosteredFormattingProvider')]
    public function test_text_inside_formatting_reconstructed_out_of_table_sections_round_trips($template)
    {
        [$parser] = $this->parseHtml($template);

        $this->assertSame($template, $parser->html()->markDirty()->toHtml());
    }

    public static function fosteredFormattingProvider()
    {
        return [
            'colgroup' => ['<table><b><colgroup><i>x<tr>'],
            'colgroup with cell' => ['<table><b><colgroup><i>x<tr><td>y'],
            'closed formatting' => ['<table><b><colgroup><i>x</i></colgroup></table>'],
            'text before the colgroup' => ['<table><b>x<colgroup><i>y<tr>'],
            'caption' => ['<table><b><caption><i>x<tr>'],
            'closed table' => ['<div><table><b><colgroup><i>x<tr></table></div>'],
            'anchor after nested table' => ['<table><nobr><caption><colgroup><a>x<table><template>'],
            'no reconstruction' => ['<table><i><colgroup>x<tr>'],
            'fostered element with text' => ['<table><div>d</div><tr><td>x</td></tr></table>'],
        ];
    }

    public function test_missing_table_bodies_and_rows_are_synthetic_and_source_transparent()
    {
        [$parser, $nodes] = $this->parseHtml('<table><td>{{ cell }}</td></table>');
        $document = $parser->html();
        $cell = $nodes[0]->parentElement();
        $row = $cell->parentElement();
        $body = $row->parentElement();

        $this->assertSame('td', $cell->name());
        $this->assertSame('tr', $row->name());
        $this->assertSame('tbody', $body->name());
        $this->assertTrue($row->isSynthetic());
        $this->assertTrue($body->isSynthetic());
        $this->assertSame('<table><td>{{ cell }}</td></table>', $document->toHtml());

        $cell->setAttribute('data-cell', 'yes');

        $this->assertSame(
            '<table><td data-cell="yes">{{ cell }}</td></table>',
            $document->toHtml()
        );
    }

    public function test_raw_text_does_not_create_phantom_elements()
    {
        [$parser, $nodes] = $this->parseHtml('<script>if (x < y) {{ value }}</script><p>{{ after }}</p>');

        $this->assertSame('script', $nodes[0]->parentElement()->name());
        $this->assertSame('p', $nodes[1]->parentElement()->name());
        $this->assertCount(2, $parser->html()->elements());
    }

    public function test_content_can_be_cleared_with_null()
    {
        [$parser] = $this->parseHtml('<div><p>x</p></div><ul>{{ items }}<li>{{ name }}</li>{{ /items }}</ul>');
        $document = $parser->html();

        $document->elements()->first(fn ($element) => $element->is('div'))->content(null);
        $document->regions()->first()->content(null);

        $this->assertSame('<div></div><ul>{{ items }}{{ /items }}</ul>', $document->toHtml());
    }

    #[DataProvider('lineEndingProvider')]
    public function test_dirty_documents_reproduce_authored_line_endings_byte_for_byte($template)
    {
        [$parser, $nodes] = $this->parseHtml($template);
        $document = $parser->html();

        $this->assertSame($template, $document->markDirty()->toHtml());

        [$parser, $nodes] = $this->parseHtml($template);
        $document = $parser->html();
        $paragraph = $nodes[0]->parentElement();
        $paragraph->setAttribute('class', 'b');
        $paragraph->nextSibling()->source('!');
        $nodes[0]->htmlNode()->source('{{ y }}');

        $this->assertSame(
            str_replace(['class="a"', '{{ x }}', '<!--c-->'], ['class="b"', '{{ y }}', '!'], $template),
            $document->toHtml()
        );
    }

    public static function lineEndingProvider()
    {
        return [
            'lf' => ["<div>\n<p\n  class=\"a\">{{ x }}</p><!--c-->\n</div>\n"],
            'crlf' => ["<div>\r\n<p\r\n  class=\"a\">{{ x }}</p><!--c-->\r\n</div>\r\n"],
            'cr' => ["<div>\r<p\r  class=\"a\">{{ x }}</p><!--c-->\r</div>\r"],
            'mixed' => ["<div>\r\n<p\r  class=\"a\">{{ x }}</p><!--c-->\n</div>\r\n\r"],
            'utf8 with crlf' => ["<div>\r\n<p class=\"a\" title=\"déjà\">café ☕ {{ x }}</p><!--c-->\r\n</div>\r\n"],
            'utf8 with mixed endings' => ["<div title=\"日本\">\r<p class=\"a\">☕ {{ x }}</p><!--c-->\r\n</div>\n"],
        ];
    }

    public function test_inserted_text_adopts_the_line_ending_only_when_the_document_uses_one_uniformly()
    {
        [$parser, $nodes] = $this->parseHtml("<div>\r\n<p>{{ x }}</p>\n</div>");
        $nodes[0]->parentElement()->after("\n<b>y</b>\n")->setAttribute('title', "a\nb");

        $this->assertSame("<div>\r\n<p title=\"a\nb\">{{ x }}</p>\n<b>y</b>\n\n</div>", $parser->html()->toHtml());

        [$parser, $nodes] = $this->parseHtml("<div>\r<p>{{ x }}</p>\r</div>");
        $nodes[0]->parentElement()->after("\n<b>y</b>\r\n");

        $this->assertSame("<div>\r<p>{{ x }}</p>\r<b>y</b>\r\r</div>", $parser->html()->toHtml());
    }

    public function test_removing_attributes_absorbs_their_whitespace()
    {
        $remove = function ($template, ...$names) {
            [$parser] = $this->parseHtml($template);
            $element = $parser->html()->first();

            foreach ($names as $name) {
                $element->removeAttribute($name);
            }

            return $parser->html()->toHtml();
        };

        $this->assertSame('<div>x</div>', $remove('<div  class="a"  >x</div>', 'class'));
        $this->assertSame('<div class="a">x</div>', $remove('<div class="a" id="b">x</div>', 'id'));
        $this->assertSame('<div id="b">x</div>', $remove('<div class="a" id="b">x</div>', 'class'));
        $this->assertSame('<div>x</div>', $remove('<div class="a" id="b" >x</div>', 'class', 'id'));
        $this->assertSame("<div\n  id=\"b\"\n>x</div>", $remove("<div\n  class=\"a\"\n  id=\"b\"\n>x</div>", 'class'));
        $this->assertSame('<br />', $remove('<br class="a" />', 'class'));
        $this->assertSame('<div {{ attrs }}>x</div>', $remove('<div class="a" {{ attrs }}>x</div>', 'class'));

        [$parser] = $this->parseHtml('<div  class="a"  >x</div>');
        $parser->html()->first()->removeAttribute('class')->setAttribute('id', 'b');

        $this->assertSame('<div id="b">x</div>', $parser->html()->toHtml());
    }

    public function test_self_closing_foreign_and_component_elements_reject_closing_tags()
    {
        [$parser] = $this->parseHtml('<svg><path/></svg><div/>x<x-{{ kind }} />');
        $document = $parser->html();
        [$svg, $path, $div, $dynamic] = $document->elements()->all();

        $div->setClosingTag('</div>');
        $svg->setClosingTag('</svg >');

        $this->assertSame('<svg><path/></svg ><div/>x<x-{{ kind }} /></div>', $document->toHtml());

        foreach ([$path, $dynamic] as $element) {
            try {
                $element->setClosingTag('</x>');
                $this->fail('Expected the closing tag to be rejected.');
            } catch (\LogicException $exception) {
                $this->assertStringContainsString('cannot have a closing tag', $exception->getMessage());
            }
        }

        $this->assertSame('<svg><path/></svg ><div/>x<x-{{ kind }} /></div>', $document->toHtml());
    }

    public function test_column_group_content_is_not_reported_as_reconstructed()
    {
        [$parser, $nodes] = $this->parseHtml('<table><caption>{{ a }}</caption><colgroup>{{ b }}<col></colgroup></table><p><b>x</p>{{ c }}');
        $placements = $parser->html()->regionPlacements();

        $this->assertFalse($placements[spl_object_id($nodes[1])]['reconstructed']);
        $this->assertFalse($nodes[1]->htmlContext()->requiresFormattingReconstruction);
        $this->assertTrue($placements[spl_object_id($nodes[2])]['reconstructed']);
        $this->assertTrue($nodes[2]->htmlContext()->requiresFormattingReconstruction);
        $this->assertSame('b', $nodes[2]->parentElement()->name());
        $this->assertTrue($nodes[2]->parentElement()->isSynthetic());
    }

    public function test_attribute_edits_keep_authored_whitespace_and_foreign_names()
    {
        [$parser] = $this->parseHtml("<a  href=\"/old\"\n   class=\"{{ classes }}\"   >x</a><svg viewBox=\"0 0 1 1\"></svg>");
        $document = $parser->html();
        [$anchor, $svg] = $document->elements()->all();

        $anchor->setAttribute('href', '/new');

        $this->assertSame(['viewBox' => '0 0 1 1'], $svg->attributes());
        $this->assertSame('0 0 1 1', $svg->attr('viewbox'));
        $this->assertSame(
            "<a  href=\"/new\"\n   class=\"{{ classes }}\"   >x</a><svg viewBox=\"0 0 1 1\"></svg>",
            $document->toHtml()
        );
    }

    #[DataProvider('attributeLineEndingProvider')]
    public function test_attribute_values_are_read_with_normalized_line_endings($eol)
    {
        $template = "<div{$eol}  class=\"a{$eol}  b\"{$eol}  title='c{$eol}d'{$eol}  data-x=e{$eol}>{{ x }}</div>{$eol}";
        [$parser] = $this->parseHtml($template);
        $document = $parser->html();
        $div = $document->first('div');

        $this->assertSame("a\n  b", $div->attr('class'));
        $this->assertSame(['class' => "a\n  b", 'title' => "c\nd", 'data-x' => 'e'], $div->attributes());
        $this->assertSame(['a', 'b'], $div->classes());
        $this->assertTrue($div->hasClass('b'));
        $this->assertSame($template, $document->markDirty()->toHtml());

        $div->setAttribute('title', "1\n2")->addClass('c');

        $this->assertSame(
            str_replace(["title='c{$eol}d'", "class=\"a{$eol}  b\""], ["title=\"1{$eol}2\"", 'class="a b c"'], $template),
            $document->toHtml()
        );
    }

    public static function attributeLineEndingProvider()
    {
        return [
            'lf' => ["\n"],
            'crlf' => ["\r\n"],
            'cr' => ["\r"],
        ];
    }

    public function test_attribute_values_with_mixed_line_endings_are_normalized_and_rendered_verbatim()
    {
        $template = "<div class=\"a\r\n b\r c\n d\"\r\n  title=\"x\r\ny\">{{ x }}</div>\n";
        [$parser] = $this->parseHtml($template);
        $document = $parser->html();
        $div = $document->first('div');

        $this->assertSame(['class' => "a\n b\n c\n d", 'title' => "x\ny"], $div->attributes());
        $this->assertSame(['a', 'b', 'c', 'd'], $div->classes());

        $div->setAttribute('title', "1\n2");

        $this->assertSame("<div class=\"a\r\n b\r c\n d\"\r\n  title=\"1\n2\">{{ x }}</div>\n", $document->toHtml());
    }

    public function test_php_blocks_inside_a_tag_do_not_swallow_the_regions_after_them()
    {
        foreach (['<?php {{ a }} ?>', '<?= {{ a }} ?>', '<?php x ?>'] as $block) {
            $template = '<div '.$block.' data-x={{ a > b }}>x</div>{{ c }}';
            [$parser, $nodes] = $this->parseHtml($template);
            $document = $parser->html();
            $div = $document->first('div');
            [$condition, $after] = array_slice($nodes, -2);

            $this->assertSame('x', $div->children()[0]->content(), $template);
            $this->assertSame('div', $condition->parentElement()->name(), $template);
            $this->assertNull($after->parentElement(), $template);
            $this->assertSame($template, $document->markDirty()->toHtml(), $template);
        }
    }

    public function test_closing_tags_are_read_without_dirtying_and_replaced_for_authored_elements()
    {
        [$parser] = $this->parseHtml('<table><td>{{ cell }}</td></table><p>a</br>b</p>');
        $document = $parser->html();
        $cell = $document->elements()->first(fn ($element) => $element->is('td'));
        $break = $document->elements()->first(fn ($element) => $element->is('br'));

        $this->assertSame('</td>', $cell->closingTag());
        $this->assertSame([], $break->attributes());
        $this->assertFalse($document->isDirty());

        $cell->setClosingTag('</td >');
        $break->setAttribute('class', 'x');

        $this->assertSame('<table><td>{{ cell }}</td ></table><p>a<br class="x">b</p>', $document->toHtml());

        foreach ([$break, $cell->parentElement()] as $element) {
            try {
                $element->setClosingTag('</x>');
                $this->fail('Expected the closing tag to be rejected.');
            } catch (\LogicException $exception) {
                $this->assertStringContainsString('cannot have a closing tag', $exception->getMessage());
            }
        }
    }

    public function test_replacing_table_content_keeps_fostered_regions_paired()
    {
        [$parser, $nodes] = $this->parseHtml('<table>{{ rows }}<tr><td>x</td></tr>{{ /rows }}</table>');
        $document = $parser->html();
        $opening = $nodes[0]->htmlNode();

        $document->elements()->first(fn ($element) => $element->is('table'))->content('<tr><td>new</td></tr>');
        $opening->before('<!--rows-->');

        $this->assertSame('<!--rows-->{{ rows }}{{ /rows }}<table><tr><td>new</td></tr></table>', $document->toHtml());
    }

    public function test_moving_a_node_out_of_the_document_marks_it_dirty()
    {
        [$parser] = $this->parseHtml('<div><p>{{ one }}</p></div>');
        $document = $parser->html();

        $document->createElement('section')->append($document->elements()->first(fn ($element) => $element->is('p')));

        $this->assertTrue($document->isDirty());
        $this->assertSame('<div></div>', $document->toHtml());
    }

    public function test_antlers_php_nodes_are_opaque_to_the_graph()
    {
        $template = '<input {{$ $field->attrs() $}} data-x="{{ x }}"><script>{{? $end = "</script>"; ?}}{{ value }}</script>{{? $open = "<textarea>"; ?}}<p>café {{ title }}</p>';
        [$parser, $nodes] = $this->parseHtml($template);
        $placements = $parser->html()->regionPlacements();
        $buckets = array_map(fn ($node) => $placements[spl_object_id($node)]['bucket'], $nodes);

        $this->assertSame(['embedded', 'raw-text', 'content'], $buckets);
        $this->assertSame('input', $placements[spl_object_id($nodes[0])]['ownerName']);
        $this->assertSame('p', $nodes[2]->parentElement()->name());
        $this->assertSame(['input', 'script', 'p'], $parser->html()->elements()->map->name()->all());
        $this->assertSame($template, $parser->html()->markDirty()->toHtml());
    }

    public function test_parser_snapshots_answer_for_their_nodes_without_keeping_the_parse_state()
    {
        $template = str_repeat("<div class=\"{{ kind }}\">\n  <p>café ☕ {{ title }}</p>\n</div>\n", 200);
        [$parser, $nodes] = $this->parseHtml($template);
        $liveLines = array_map(fn ($node) => $node->relativePositionFromOffset(0, 0)->line, $nodes);
        $snapshot = $parser->snapshot();

        foreach ($parser->getNodes() as $node) {
            if ($node instanceof AntlersNode) {
                $node->withParser($snapshot);
            }
        }

        $parser->parse('<aside>{{ other }}</aside>');

        $this->assertSame('p', $nodes[1]->parentElement()->name());
        $this->assertSame('attribute-value', $nodes[0]->htmlContext()->kind);
        $this->assertSame('{{ title }}', $nodes[1]->documentText());
        $this->assertSame([], $snapshot->getRenderNodes());
        $this->assertCount(count($nodes) + 401, $snapshot->getNodes());

        $this->assertSame([1, 2, 4, 5], array_slice($liveLines, 0, 4));
        $this->assertSame($liveLines, array_map(fn ($node) => $node->relativePositionFromOffset(0, 0)->line, $nodes));
        $this->assertSame(array_map(fn ($node) => $node->startPosition->line, $nodes), $liveLines);
        $this->assertSame(599, $nodes[399]->relativePositionFromOffset(0, 0)->line);
    }

    public function test_repeated_adoption_against_one_block_does_not_multiply_source_anchors()
    {
        $count = 200;
        $template = implode('', array_map(fn ($i) => '<b id="'.$i.'">', range(1, $count))).'<div>x{{ a }}'.str_repeat('</b>', $count);
        [$parser] = $this->parseHtml($template);
        $document = $parser->html();

        $this->assertLessThan($count * 8, count($document->arena()->kinds));
        $this->assertSame($template, $document->markDirty()->toHtml());
    }

    public function test_insertion_chains_stop_growing_past_the_browser_depth_limit()
    {
        $template = str_repeat('<div>', 600).'<p>{{ a }}';
        [$parser, $nodes] = $this->parseHtml($template);
        $document = $parser->html();
        $chain = $document->regionPlacements()[spl_object_id($nodes[0])]['chain'];

        $this->assertCount(512, $chain);
        $this->assertSame('p', end($chain));
        $this->assertSame('p', $nodes[0]->parentElement()->name());
        $this->assertSame($template, $document->markDirty()->toHtml());
    }

    public function test_php_blocks_inside_a_tag_are_skipped_by_every_tag_scanner()
    {
        $anchor = '<a href="<?= url("/foo") ?>" data-x="1">';
        $template = $anchor.'{{ y }}</a><svg><path d=<?= $d ?>/>{{ z }}</svg>';
        [$parser, $nodes] = $this->parseHtml($template);
        $document = $parser->html();
        $inspector = new ElementInspector;

        $this->assertSame('1', $document->first('a')->attr('data-x'));
        $this->assertSame('<?= url("/foo") ?>', $document->first('a')->attr('href'));
        $this->assertSame($anchor, $inspector->openingTagAt($template, 0));
        $this->assertTrue($inspector->hasAttribute($anchor, 'data-x'));
        $this->assertFalse($inspector->hasAttribute($anchor, 'url'));
        $this->assertFalse($document->first('path')->isSelfClosing());
        $this->assertSame('path', $nodes[1]->parentElement()->name());
        $this->assertSame($template, $document->markDirty()->toHtml());
    }

    public function test_regions_inside_a_php_block_inside_a_tag_keep_their_attribute()
    {
        $markup = '<a href="<?= f({{ x }}) ?>" {{ y }}>';
        $positions = (new ElementInspector)->classifyTagPositions($markup, [strpos($markup, '{{ x }}'), strpos($markup, '{{ y }}')]);

        $this->assertSame([HtmlContext::KIND_ATTRIBUTE_VALUE, 'href', ''], $positions[strpos($markup, '{{ x }}')]);
        $this->assertSame(HtmlContext::KIND_TAG_OPEN, $positions[strpos($markup, '{{ y }}')][0]);
    }

    public function test_region_extents_follow_antlers_comment_and_php_terminators()
    {
        foreach (['{{# x }} y #}}', '{{? $x = "}}"; ?}}', '{{$ "}}" $}}', '{{ a | b:{c} }}'] as $region) {
            $markup = '<a '.$region.' b="{{ c }}">';

            $this->assertSame(3 + strlen($region), RegionExtents::endInBytes($markup, 3, strlen($markup)), $region);
        }

        $this->assertFalse(RegionExtents::endInBytes('<a {{# x }}>', 3, 12));
    }

    protected function parseHtml($template)
    {
        $parser = new DocumentParser();
        $parser->inheritRuntimeLineSeed(false);
        $parser->parse($template);
        $nodes = collect($parser->getNodes())
            ->filter(fn ($node) => $node instanceof AntlersNode && ! $node->isClosingTag)
            ->values()
            ->all();

        return [$parser, $nodes];
    }
}
