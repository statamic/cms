<?php

namespace Statamic\Markdown;

use Closure;
use League\CommonMark\CommonMarkConverter;

interface ParserContract
{
    public function __construct(array $config = []);

    public function parse(string $markdown): string;

    public function converter(): CommonMarkConverter;

    public function addExtension(Closure $closure): self;

    public function addExtensions(Closure $closure): self;

    public function extensions(): array;

    public function withStatamicDefaults();

    public function withAutoLinks(): self;

    public function withAutoLineBreaks(): self;

    public function withMarkupEscaping(): self;

    public function withSmartPunctuation(): self;

    public function newInstance(array $config = []);
}
