<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Markdown\Manager;
use Statamic\Markdown\Parser;

/**
 * @method static Parser makeParser(array $config = [])
 * @method static Parser parser(string $name)
 * @method static bool hasParser(string $name)
 * @method static void extend(string $name, \Closure $closure)
 * @method static string parse(string $markdown)
 * @method static \League\CommonMark\CommonMarkConverter converter()
 * @method static \League\CommonMark\Environment\Environment environment()
 * @method static Parser addExtension(\Closure $closure)
 * @method static Parser addExtensions(\Closure $closure)
 * @method static array extensions()
 * @method static void withStatamicDefaults()
 * @method static Parser withAutoLinks()
 * @method static Parser withAutoLineBreaks()
 * @method static Parser withMarkupEscaping()
 * @method static Parser withSmartPunctuation()
 * @method static Parser withTableOfContents()
 * @method static Parser withHeadingPermalinks()
 * @method static mixed config(string $key = null)
 * @method static void newInstance(array $config = [])
 *
 * @see \Statamic\Markdown\Manager
 */
class Markdown extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
