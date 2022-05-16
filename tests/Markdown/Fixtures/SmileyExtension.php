<?php

namespace Tests\Markdown\Fixtures;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

class SmileyExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new SmileyParser);
    }
}

class SmileyParser implements InlineParserInterface
{
    protected $emoji = '😀';
    protected $char = ')';

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string(':');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();

        $nextChar = $cursor->peek();

        if ($nextChar !== $this->char) {
            return false;
        }

        $cursor->advanceBy(2);

        if ($nextChar === $this->char) {
            $inlineContext->getContainer()->appendChild(new Text($this->emoji));
        }

        return true;
    }
}
