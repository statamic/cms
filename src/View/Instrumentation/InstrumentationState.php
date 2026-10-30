<?php

namespace Statamic\View\Instrumentation;

use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\Antlers\ComponentSourceMap;

/** @internal */
class InstrumentationState
{
    const INDICATOR_TWINS = '_io';

    protected static int $componentViewDepth = 0;

    protected static int $viewInstrumentationDepth = 0;

    protected static bool $suppressHtmlInstrumentation = false;

    protected static ?ComponentSourceMap $sourceMap = null;

    protected static int $lineSeed = 1;

    protected static int $offsetSeed = 0;

    protected static bool $attributesView = true;

    protected static ?string $twinsKey = null;

    protected static string $twinsEnvironment = '';

    /** @var TagBody[] */
    protected static array $tagBodies = [];

    /** @var array<int, array{0: OutputContext, 1: string}> */
    protected static array $outputContexts = [];

    public static function twinsKey(): string
    {
        if (self::$twinsKey === null || self::$twinsEnvironment !== GlobalRuntimeState::$environmentId) {
            self::$twinsEnvironment = GlobalRuntimeState::$environmentId;
            self::$twinsKey = GlobalRuntimeState::createIndicatorVariable(self::INDICATOR_TWINS);
        }

        return self::$twinsKey;
    }

    public static function sourceMap(): ?ComponentSourceMap
    {
        return self::$sourceMap;
    }

    public static function lineSeed(): int
    {
        return self::$lineSeed;
    }

    public static function offsetSeed(): int
    {
        return self::$offsetSeed;
    }

    public static function attributesView(): bool
    {
        return self::$attributesView;
    }

    /** @param  array{0: int, 1: int, 2: bool}  $seed */
    public static function withSourceSeed(array $seed, callable $callback): mixed
    {
        $previous = [self::$lineSeed, self::$offsetSeed, self::$attributesView];
        [self::$lineSeed, self::$offsetSeed, self::$attributesView] = $seed;

        try {
            return $callback();
        } finally {
            [self::$lineSeed, self::$offsetSeed, self::$attributesView] = $previous;
        }
    }

    public static function withSourceMap(?ComponentSourceMap $sourceMap, callable $callback): mixed
    {
        $previous = self::$sourceMap;
        self::$sourceMap = $sourceMap;

        try {
            return $callback();
        } finally {
            self::$sourceMap = $previous;
        }
    }

    public static function inComponentView(): bool
    {
        return self::$componentViewDepth > 0;
    }

    public static function instrumenting(): bool
    {
        return self::$viewInstrumentationDepth > 0;
    }

    public static function suppressed(): bool
    {
        return self::$suppressHtmlInstrumentation;
    }

    public static function withoutInstrumentation(callable $callback): mixed
    {
        $previous = self::$suppressHtmlInstrumentation;
        self::$suppressHtmlInstrumentation = true;

        try {
            return $callback();
        } finally {
            self::$suppressHtmlInstrumentation = $previous;
        }
    }

    public static function withinComponentView(callable $callback): mixed
    {
        self::$componentViewDepth++;

        try {
            return $callback();
        } finally {
            self::$componentViewDepth--;
        }
    }

    public static function whileInstrumenting(callable $callback): mixed
    {
        self::$viewInstrumentationDepth++;

        try {
            return $callback();
        } finally {
            self::$viewInstrumentationDepth--;
        }
    }

    public static function bufferedOutputIsCommentSafe(): bool
    {
        $level = ob_get_level();

        if ($level === 0) {
            return true;
        }

        $length = ob_get_length();

        if ($length === false || $length === 0) {
            unset(self::$outputContexts[$level]);

            return true;
        }

        $buffered = ob_get_contents();
        [$context, $scannedHash] = self::$outputContexts[$level] ?? [null, ''];

        if ($context === null
            || $context->length() > $length
            || hash('xxh3', substr($buffered, 0, $context->length())) !== $scannedHash) {
            $context = new OutputContext;
        }

        $context->advance($buffered);
        self::$outputContexts[$level] = [$context, hash('xxh3', substr($buffered, 0, $context->length()))];

        return $context->isCommentSafe();
    }

    public static function activeTagBody(): ?TagBody
    {
        return self::$tagBodies === [] ? null : self::$tagBodies[count(self::$tagBodies) - 1];
    }

    public static function withTagBody(?TagBody $body, callable $callback): mixed
    {
        if ($body === null) {
            return $callback();
        }

        self::$tagBodies[] = $body;

        try {
            return $callback();
        } finally {
            array_pop(self::$tagBodies);
        }
    }
}
