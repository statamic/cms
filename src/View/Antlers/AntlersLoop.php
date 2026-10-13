<?php

namespace Statamic\View\Antlers;

use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\InstrumentationState;

class AntlersLoop extends AntlersString
{
    protected $parser;
    protected $string;
    protected $variables;
    protected $supplement;
    protected $context;
    protected $trusted;
    protected $suppressesInstrumentation;
    protected $tagBody;

    public function __construct($parser, $string, $variables, $supplement, $context, $trusted = false)
    {
        $this->parser = $parser;
        $this->string = $string;
        $this->variables = $variables;
        $this->supplement = $supplement;
        $this->context = $context;
        $this->trusted = $trusted;
        $this->suppressesInstrumentation = InstrumentationState::suppressed();
        $this->tagBody = InstrumentationState::activeTagBody();
    }

    public function __toString()
    {
        return $this->render()[0];
    }

    public function instrumented()
    {
        return $this->render()[1];
    }

    /** @return array{0: string, 1: string} */
    private function render()
    {
        $previousIsEvaluatingUserData = GlobalRuntimeState::$isEvaluatingUserData;
        GlobalRuntimeState::$isEvaluatingUserData = ! $this->trusted;

        try {
            $render = fn () => InstrumentationState::withTagBody($this->tagBody, fn () => $this->renderLoopContent());

            if ($this->suppressesInstrumentation) {
                return InstrumentationState::withoutInstrumentation($render);
            }

            return $render();
        } finally {
            GlobalRuntimeState::$isEvaluatingUserData = $previousIsEvaluatingUserData;
        }
    }

    private function renderLoopContent()
    {
        $total = count($this->variables);
        $i = 0;
        $hasInstrumentation = false;

        $instrumented = collect($this->variables)->reduce(function ($carry, $item) use (&$i, &$hasInstrumentation, $total) {
            if ($this->supplement) {
                $item = array_merge($item, [
                    'index' => $i,
                    'count' => $i + 1,
                    'total_results' => $total,
                    'first' => ($i === 0),
                    'last' => ($i === $total - 1),
                ]);
            }

            $i++;

            $parsed = $this->parser
                ->parse($this->string, array_merge($this->context, $item))
                ->withoutExtractions();

            if ($parsed instanceof AntlersString && $parsed->hasInstrumentation()) {
                $hasInstrumentation = true;

                return $carry.$parsed->instrumented();
            }

            return $carry.$parsed;
        }, '');

        if ($this->injectExtractions) {
            $instrumented = $this->parser->injectNoparse($instrumented);
        }

        if (! $hasInstrumentation) {
            return [$instrumented, $instrumented];
        }

        return [Runtime::removeMarkers($instrumented), $instrumented];
    }
}
