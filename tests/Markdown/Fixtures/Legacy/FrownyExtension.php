<?php

namespace Tests\Markdown\Fixtures\Legacy;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;

class FrownyExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment->addInlineParser(new FrownyParser);
    }
}

class FrownyParser extends SmileyParser
{
    protected $emoji = '🙁';
    protected $char = '(';
}
