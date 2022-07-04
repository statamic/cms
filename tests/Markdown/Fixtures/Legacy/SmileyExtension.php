<?php

namespace Tests\Markdown\Fixtures\Legacy;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;

class SmileyExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment): void
    {
        $environment->addInlineParser(new SmileyParser);
    }
}

class SmileyParser implements InlineParserInterface
{
    protected $emoji = '😀';
    protected $char = ')';

    public function getCharacters(): array
    {
        return [':'];
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
