<?php

namespace Tests\Markdown\Fixtures;

use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

class HeadingRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! ($node instanceof Heading)) {
            throw new \InvalidArgumentException('Incompatible node type: '.get_class($node));
        }

        return "<h1 data-custom-renderer>{$childRenderer->renderNodes($node->children())}</h1>";
    }
}
