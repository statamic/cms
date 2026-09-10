<?php

namespace Statamic\View\Instrumentation;

use Statamic\View\Instrumentation\Antlers\ComponentSourceMap;

/** @internal */
class InstrumentationState
{
    protected static $componentContentDepth = 0;

    protected static $componentViewDepth = 0;

    protected static $viewInstrumentationDepth = 0;

    protected static bool $suppressHtmlInstrumentation = false;

    protected static ?ComponentSourceMap $sourceMap = null;

    public static function sourceMap(): ?ComponentSourceMap
    {
        return self::$sourceMap;
    }

    public static function withSourceMap(?ComponentSourceMap $sourceMap, callable $callback)
    {
        $previous = self::$sourceMap;
        self::$sourceMap = $sourceMap;

        try {
            return $callback();
        } finally {
            self::$sourceMap = $previous;
        }
    }

    public static function parsingComponentContent()
    {
        return self::$componentContentDepth > 0;
    }

    public static function renderingComponentView()
    {
        return self::$componentViewDepth > 0;
    }

    public static function instrumentingView()
    {
        return self::$viewInstrumentationDepth > 0;
    }

    public static function suppressesHtmlInstrumentation(): bool
    {
        return self::$suppressHtmlInstrumentation;
    }

    public static function whileRenderingInContext(?HtmlContext $context, callable $callback)
    {
        $previous = self::$suppressHtmlInstrumentation;
        self::$suppressHtmlInstrumentation = $previous
            || ($context !== null && ! $context->isSafeForHtmlComments());

        try {
            return $callback();
        } finally {
            self::$suppressHtmlInstrumentation = $previous;
        }
    }

    public static function whileParsingComponentContent(callable $callback)
    {
        self::$componentContentDepth++;

        try {
            return $callback();
        } finally {
            self::$componentContentDepth--;
        }
    }

    public static function whileRenderingComponentView(callable $callback)
    {
        self::$componentViewDepth++;

        try {
            return $callback();
        } finally {
            self::$componentViewDepth--;
        }
    }

    public static function whileInstrumentingView(callable $callback)
    {
        self::$viewInstrumentationDepth++;

        try {
            return $callback();
        } finally {
            self::$viewInstrumentationDepth--;
        }
    }
}
