<?php

namespace Statamic\View\Instrumentation;

use InvalidArgumentException;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\Tracing\NodeVisitorContract;
use Statamic\View\Antlers\Language\Runtime\Tracing\RuntimeTracerContract;
use WeakMap;

/** @internal */
class InstrumentationManager
{
    const ENGINE_ANTLERS = Span::ENGINE_ANTLERS;

    /** @var WeakMap<RuntimeConfiguration, bool> */
    protected WeakMap $runtimeConfigurations;

    /** @var array<string, object> */
    protected $configured = [];

    /** @var array<int, HtmlInstrumentation|RuntimeTracerContract|TracerContract> */
    protected $antlersPieces = [];

    /** @var callable[] */
    protected $runtimePreparsers = [];

    /** @var NodeVisitorContract[] */
    protected $runtimeVisitors = [];

    public function __construct()
    {
        $this->runtimeConfigurations = new WeakMap;
    }

    public function preparse(callable $preparser): void
    {
        if (! in_array($preparser, $this->runtimePreparsers, true)) {
            $this->runtimePreparsers[] = $preparser;
        }

        $this->applyToAntlers(fn (RuntimeConfiguration $config) => $config->preparse($preparser));
    }

    public function addVisitor(NodeVisitorContract $visitor): void
    {
        if (! in_array($visitor, $this->runtimeVisitors, true)) {
            $this->runtimeVisitors[] = $visitor;
        }

        $this->applyToAntlers(fn (RuntimeConfiguration $config) => $config->addVisitor($visitor));
    }

    /**
     * @param  object|string  $piece
     * @param  string[]|null  $engines
     * @return $this
     */
    public function register($piece, ?array $engines = null)
    {
        $engines = $this->normalizeEngines($engines);
        $abstract = null;

        if (is_string($piece)) {
            $abstract = $piece;
            $piece = $this->configured[$abstract] ?? app($abstract);
        }

        if ($piece instanceof HtmlInstrumentation) {
            $this->registerHtmlInstrumentation($piece, $engines);
        } elseif ($piece instanceof RuntimeTracerContract || $piece instanceof TracerContract) {
            $this->registerTracer($piece, $engines);
        } else {
            throw new InvalidArgumentException(
                'Instrumentation pieces must be an HtmlInstrumentation instance, a TracerContract, or a RuntimeTracerContract.'
            );
        }

        if ($abstract !== null) {
            $this->configured[$abstract] ??= $piece;
        }

        return $this;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return HtmlInstrumentation
     */
    public function configuredHtmlInstrumentation(array $config)
    {
        $key = $this->configurationKey($config);

        if ($key === null) {
            return HtmlInstrumentation::fromConfig($config);
        }

        return $this->configured[$key] ??= HtmlInstrumentation::fromConfig($config);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return string|null
     */
    protected function configurationKey(array $config)
    {
        $fingerprint = HtmlInstrumentation::configurationFingerprint($config);

        return $fingerprint === null ? null : 'html:'.$fingerprint;
    }

    /**
     * @param  object|string  $piece
     * @param  string[]|null  $engines
     * @return $this
     */
    public function forget($piece, ?array $engines = null)
    {
        $engines = $this->normalizeEngines($engines);

        if (is_string($piece)) {
            $resolved = $this->configured[$piece] ?? null;

            if ($resolved === null) {
                return $this;
            }

            if ($engines === null) {
                unset($this->configured[$piece]);
            }

            $piece = $resolved;
        }

        $wantsAntlers = $engines === null || in_array(static::ENGINE_ANTLERS, $engines, true);

        if ($wantsAntlers) {
            $this->antlersPieces = $this->without($this->antlersPieces, $piece);

            $this->removeFromAntlers($piece);
        }

        return $this;
    }

    /** @return $this */
    public function flush()
    {
        $antlers = $this->antlersPieces;

        $this->configured = [];
        $this->antlersPieces = [];

        foreach ($antlers as $piece) {
            $this->removeFromAntlers($piece);
        }

        return $this;
    }

    protected function removeFromAntlers($piece): void
    {
        $this->applyToAntlers(function (RuntimeConfiguration $config) use ($piece) {
            if ($piece instanceof HtmlInstrumentation) {
                $config->removePreparser($piece);

                return;
            }

            $config->traceManager?->removeTracer($piece);
        });
    }

    /** @return void */
    protected function rememberAntlersPiece($piece)
    {
        if (! in_array($piece, $this->antlersPieces, true)) {
            $this->antlersPieces[] = $piece;
        }
    }

    /**
     * @template T
     *
     * @param  T[]  $items
     * @return T[]
     */
    protected function without(array $items, $needle)
    {
        return array_values(array_filter($items, fn ($item) => $item !== $needle));
    }

    /** @return void */
    public function applyTo(RuntimeConfiguration $runtimeConfiguration)
    {
        // Removing our tracers must preserve tracing enabled by the parser itself.
        $this->runtimeConfigurations[$runtimeConfiguration] ??= $runtimeConfiguration->isTracingEnabled;

        $config = config('statamic.antlers.instrumentation', []);

        if (is_array($config) && ($config['enabled'] ?? false)) {
            $markerConfig = array_intersect_key($config, array_flip(['comments', 'prefix', 'attributes']));
            $runtimeConfiguration->preparse($this->configuredHtmlInstrumentation($markerConfig));
        }

        foreach ($this->runtimePreparsers as $preparser) {
            $runtimeConfiguration->preparse($preparser);
        }

        foreach ($this->runtimeVisitors as $visitor) {
            $runtimeConfiguration->addVisitor($visitor);
        }

        foreach ($this->antlersPieces as $piece) {
            if ($piece instanceof HtmlInstrumentation) {
                $runtimeConfiguration->preparse($piece);

                continue;
            }

            $runtimeConfiguration->enableTracing()->registerTracer($piece);
        }
    }

    /**
     * @param  RuntimeTracerContract|TracerContract  $tracer
     * @param  string[]|null  $engines
     * @return $this
     */
    protected function registerTracer($tracer, ?array $engines)
    {
        $engines ??= [static::ENGINE_ANTLERS];

        if (in_array(static::ENGINE_ANTLERS, $engines, true)) {
            $this->rememberAntlersPiece($tracer);

            $this->applyToAntlers(function (RuntimeConfiguration $config) use ($tracer) {
                $config->enableTracing()->registerTracer($tracer);
            });
        }

        return $this;
    }

    /**
     * @param  string[]|null  $engines
     * @return $this
     */
    protected function registerHtmlInstrumentation(HtmlInstrumentation $instrumentation, ?array $engines)
    {
        $wantsAntlers = $engines === null || in_array(static::ENGINE_ANTLERS, $engines, true);

        if ($wantsAntlers) {
            $this->rememberAntlersPiece($instrumentation);
            $this->applyToAntlers(fn (RuntimeConfiguration $config) => $config->preparse($instrumentation));
        }

        return $this;
    }

    /** @return void */
    protected function applyToAntlers(callable $apply)
    {
        foreach ($this->runtimeConfigurations as $configuration => $tracingEnabled) {
            $apply($configuration);
            $configuration->isTracingEnabled = $tracingEnabled || $configuration->hasRegisteredTracers();
        }
    }

    /**
     * @param  string[]|null  $engines
     * @return string[]|null
     */
    protected function normalizeEngines(?array $engines)
    {
        if ($engines === null) {
            return null;
        }

        foreach ($engines as $engine) {
            if (! is_string($engine)) {
                throw new InvalidArgumentException('Instrumentation engine names must be strings.');
            }
        }

        $engines = array_values(array_unique(array_map('strtolower', $engines)));

        if ($engines === []) {
            throw new InvalidArgumentException('At least one engine must be provided.');
        }

        foreach ($engines as $engine) {
            if (! in_array($engine, [static::ENGINE_ANTLERS], true)) {
                throw new InvalidArgumentException("Unknown instrumentation engine [{$engine}].");
            }
        }

        return $engines;
    }
}
