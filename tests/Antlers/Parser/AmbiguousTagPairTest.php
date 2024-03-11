<?php

namespace Tests\Antlers\Parser;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Tests\Antlers\ParserTestCase;

class AmbiguousTagPairTest extends ParserTestCase
{
    public function test_self_closing_tags_are_not_considered_during_matching()
    {
        $template = <<<'EOT'
{{ array }}
<p>zero</p>
{{ array /}}
<p>one</p>
{{ array /}}
<p>two</p>
{{ /array }}
EOT;

        $parsedNodes = $this->parseNodes($template);
        $this->assertCount(7, $parsedNodes);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[0]);
        $this->assertNotNull($parsedNodes[0]->isClosedBy);
        $this->assertSame($parsedNodes[6], $parsedNodes[0]->isClosedBy);

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[1]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[2]);
        $this->assertTrue($parsedNodes[2]->isSelfClosing);

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[3]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[4]);
        $this->assertTrue($parsedNodes[4]->isSelfClosing);

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[5]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[6]);
        $this->assertNotNull($parsedNodes[6]->isOpenedBy);
        $this->assertSame($parsedNodes[0], $parsedNodes[6]->isOpenedBy);
    }

    public function test_tags_with_similar_names_match_against_the_compound_name()
    {
        $template = <<<'EOT'
<nav class="flex items-center justify-between flex-wrap py-12 lg:py-24 max-w-5xl mx-auto">
    <div class="text-sm">&copy; {{ now format="Y" }} {{ settings:site_name }}
        – Powered by <a href="https://statamic.com?ref=cool-writings" class="hover:text-teal">Statamic</a></div>
    <div class="flex items-center">
        {{ settings:social }}
            <a href="{{ url }}" class="ml-8" aria-label="{{ name }}" rel="noopener">
                {{ svg :src="icon" class="h-6 w-6 hover:text-teal" }}
            </a>
        {{ /settings:social }}
    </div>
</nav>
EOT;

        $parsedNodes = $this->parseNodes($template);
        $this->assertCount(15, $parsedNodes);
        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[0]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[1]);
        $this->assertNull($parsedNodes[1]->isClosedBy);

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[2]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[3]);
        $this->assertNull($parsedNodes[3]->isClosedBy); // settings:site_name should not be paired in this template.

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[4]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[5]); // settings:social
        $this->assertNotNull($parsedNodes[5]->isClosedBy);
        $this->assertNull($parsedNodes[5]->isOpenedBy);
        $this->assertNotNull($parsedNodes[5]->isClosedBy);
        $this->assertSame($parsedNodes[13], $parsedNodes[5]->isClosedBy);

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[6]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[7]);
        $this->assertNull($parsedNodes[7]->isOpenedBy);
        $this->assertNull($parsedNodes[7]->isClosedBy);

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[8]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[9]);
        $this->assertNull($parsedNodes[9]->isOpenedBy);
        $this->assertNull($parsedNodes[9]->isClosedBy);

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[10]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[11]);
        $this->assertNull($parsedNodes[11]->isOpenedBy);
        $this->assertNull($parsedNodes[11]->isClosedBy);

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[12]);
        $this->assertInstanceOf(AntlersNode::class, $parsedNodes[13]); // /settings:social
        $this->assertNull($parsedNodes[13]->isClosedBy);
        $this->assertNotNull($parsedNodes[13]->isOpenedBy);
        $this->assertSame($parsedNodes[5], $parsedNodes[13]->isOpenedBy);

        $this->assertInstanceOf(LiteralNode::class, $parsedNodes[14]);
    }

    public function test_parser_correctly_associates_ambiguous_tags_with_self_closing_tags()
    {
        $template = <<<'EOT'
{{ partial src="wrapper" }}
    {{ partial src="second_wrapper" }}
        {{ partial src="content" /}}
    {{ /partial }}
{{ /partial }}
EOT;

        /** @var AbstractNode[] $nodes */
        $nodes = $this->parseNodes($template);

        /** @var AntlersNode $firstPartial */
        $firstPartial = $nodes[0];
        /** @var AntlersNode $firstPartialClose */
        $firstPartialClose = $nodes[8];

        $this->assertSame(' partial src="wrapper" ', $firstPartial->content);
        $this->assertSame(' /partial ', $firstPartialClose->content);
        $this->assertSame($firstPartial, $firstPartialClose->isOpenedBy);
        $this->assertSame($firstPartialClose, $firstPartial->isClosedBy);

        /** @var AntlersNode $secondPartial */
        $secondPartial = $nodes[2];
        /** @var AntlersNode $secondPartialClose */
        $secondPartialClose = $nodes[6];

        $this->assertSame(' partial src="second_wrapper" ', $secondPartial->content);
        $this->assertSame(' /partial ', $secondPartialClose->content);
        $this->assertSame($secondPartial, $secondPartialClose->isOpenedBy);
        $this->assertSame($secondPartialClose, $secondPartial->isClosedBy);

        /** @var AntlersNode $thirdPartial */
        $thirdPartial = $nodes[4];
        $this->assertSame(' partial src="content" ', $thirdPartial->content);
        $this->assertNull($thirdPartial->isClosedBy);
        $this->assertNull($thirdPartial->isOpenedBy);
        $this->assertTrue($thirdPartial->isSelfClosing);

        $this->assertCount(9, $nodes);
    }

    public function test_parser_correctly_associates_nested_collection_tag_pairs()
    {
        $template = <<<'EOT'
{{ collection from="blog" }}
  {{ collection :from="related_collection" }}
    {{ title }}
  {{ /collection }}
{{ /collection }}
EOT;

        /** @var AbstractNode[] $nodes */
        $nodes = $this->parseNodes($template);

        $this->assertCount(9, $nodes);

        /** @var AntlersNode $firstCollectionOpen */
        $firstCollectionOpen = $nodes[0];

        /** @var AntlersNode $firstCollectionClose */
        $firstCollectionClose = $nodes[8];

        $this->assertSame(' collection from="blog" ', $firstCollectionOpen->content);
        $this->assertSame(' /collection ', $firstCollectionClose->content);
        $this->assertSame($firstCollectionClose, $firstCollectionOpen->isClosedBy);
        $this->assertSame($firstCollectionOpen, $firstCollectionClose->isOpenedBy);

        /** @var AntlersNode $secondCollectionOpen */
        $secondCollectionOpen = $nodes[2];

        /** @var AntlersNode $secondCollectionClose */
        $secondCollectionClose = $nodes[6];

        $this->assertSame(' collection :from="related_collection" ', $secondCollectionOpen->content);
        $this->assertSame(' /collection ', $secondCollectionClose->content);

        $this->assertSame($secondCollectionClose, $secondCollectionOpen->isClosedBy);
        $this->assertSame($secondCollectionOpen, $secondCollectionClose->isOpenedBy);

        $this->assertNotSame($secondCollectionClose, $firstCollectionClose);
        $this->assertNotSame($secondCollectionOpen, $firstCollectionOpen);
    }

    public function test_double_colon_method_parts_can_be_paired()
    {
        $template = <<<'ANTLERS'
{{ partial:some::template/path }}
    {{ partial src="something" }}
    
    {{ /partial }}
{{ /partial:some::template/path }}
ANTLERS;
        $nodes = $this->parseNodes($template);

        /** @var AntlersNode $p1 */
        $p1 = $nodes[0];
        /** @var AntlersNode $p2 */
        $p2 = $nodes[2];
        /** @var AntlersNode $p3 */
        $p3 = $nodes[4];
        /** @var AntlersNode $p4 */
        $p4 = $nodes[6];

        $this->assertSame($p4->isOpenedBy, $p1);
        $this->assertNull($p4->isClosedBy);
        $this->assertSame($p4, $p1->isClosedBy);

        $this->assertSame($p3->isOpenedBy, $p2);
        $this->assertNull($p3->isClosedBy);
        $this->assertSame($p3, $p2->isClosedBy);
    }
}
