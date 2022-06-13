<?php

namespace Tests\Markdown\Fixtures;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

class FrownyExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new FrownyParser);
    }
}

class FrownyParser extends SmileyParser
{
    protected $emoji = '🙁';
    protected $char = '(';
}
