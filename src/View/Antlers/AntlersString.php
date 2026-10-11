<?php

namespace Statamic\View\Antlers;

use Statamic\Contracts\View\Antlers\Parser;
use Statamic\View\Instrumentation\InstrumentationManager;

class AntlersString
{
    protected $string;
    protected $parser;
    protected $injectExtractions = true;
    protected $instrumentedString = null;
    protected $instrumentation = null;

    public function __construct(string $string, Parser $parser)
    {
        $this->string = $string;
        $this->parser = $parser;
    }

    /**
     * @internal
     */
    public static function withInstrumentation(string $rendered, Parser $parser, InstrumentationManager $instrumentation): static
    {
        $instance = new static('', $parser);
        $instance->string = null;
        $instance->instrumentedString = $rendered;
        $instance->instrumentation = $instrumentation;

        return $instance;
    }

    public function withoutExtractions()
    {
        $this->injectExtractions = false;

        return $this;
    }

    /** @internal */
    public function hasInstrumentation(): bool
    {
        return $this->instrumentedString !== null;
    }

    /**
     * @internal
     *
     * @return string The rendered output with its instrumentation markers.
     */
    public function instrumented()
    {
        return $this->finish($this->instrumentedString ?? $this->string);
    }

    public function __toString()
    {
        if ($this->instrumentedString !== null) {
            $this->string ??= $this->instrumentation->removeMarkers($this->instrumentedString);
        }

        return $this->finish($this->string);
    }

    protected function finish($string)
    {
        return $this->injectExtractions
            ? $this->parser->injectNoparse($string)
            : $string;
    }
}
