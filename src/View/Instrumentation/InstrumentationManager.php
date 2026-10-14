<?php

namespace Statamic\View\Instrumentation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
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
    protected array $configured = [];

    /** @var array<int, HtmlInstrumentation|RuntimeTracerContract|TracerContract> */
    protected array $antlersPieces = [];

    /** @var array{0: mixed, 1: HtmlInstrumentation|null}|null */
    protected ?array $configuredState = null;

    /** @var HtmlInstrumentation[]|null */
    protected ?array $htmlInstrumenters = null;

    /** @var string[] */
    protected array $cleanedWith = [];

    protected ?string $cleanedHash = null;

    protected int $cleanedLength = -1;

    protected int $misplacedMarkerRemovals = 0;

    protected int $removedMisplacedMarkerBytes = 0;

    /** @var callable[] */
    protected array $runtimePreparsers = [];

    /** @var NodeVisitorContract[] */
    protected array $runtimeVisitors = [];

    public function __construct()
    {
        $this->runtimeConfigurations = new WeakMap;
    }

    public function preparse(callable $preparser): static
    {
        if (! in_array($preparser, $this->runtimePreparsers, true)) {
            $this->runtimePreparsers[] = $preparser;
        }

        $this->applyToAntlers(fn (RuntimeConfiguration $config) => $config->preparse($preparser));

        return $this;
    }

    public function removePreparser(callable $preparser): static
    {
        $this->runtimePreparsers = $this->without($this->runtimePreparsers, $preparser);

        $this->applyToAntlers(fn (RuntimeConfiguration $config) => $config->removePreparser($preparser));

        return $this;
    }

    public function addVisitor(NodeVisitorContract $visitor): static
    {
        if (! in_array($visitor, $this->runtimeVisitors, true)) {
            $this->runtimeVisitors[] = $visitor;
            RuntimeParser::clearRenderNodeCache();
        }

        $this->applyToAntlers(fn (RuntimeConfiguration $config) => $config->addVisitor($visitor));

        return $this;
    }

    public function removeVisitor(NodeVisitorContract $visitor): static
    {
        if (in_array($visitor, $this->runtimeVisitors, true)) {
            $this->runtimeVisitors = $this->without($this->runtimeVisitors, $visitor);
            RuntimeParser::clearRenderNodeCache();
        }

        $this->applyToAntlers(fn (RuntimeConfiguration $config) => $config->removeVisitor($visitor));

        return $this;
    }

    /** @param  string[]|null  $engines */
    public function register(object|string $piece, ?array $engines = null): static
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
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @param  string[]|null  $engines
     * @return TReturn
     */
    public function using(object|string $piece, callable $callback, ?array $engines = null): mixed
    {
        if (is_string($piece)) {
            $piece = $this->configured[$piece] ?? app($piece);
        }

        $registered = in_array($piece, $this->antlersPieces, true);

        $this->register($piece, $engines);

        try {
            return $callback();
        } finally {
            if (! $registered) {
                $this->forget($piece, $engines);
            }
        }
    }

    /** @return Collection<int, array<string, mixed>> */
    public function regions(string $html): Collection
    {
        return HtmlInstrumentation::regionsIn($html, $this->htmlInstrumenters());
    }

    /** @param  array<string, mixed>  $config */
    public function configuredHtmlInstrumentation(array $config): HtmlInstrumentation
    {
        $key = $this->configurationKey($config);

        if ($key === null) {
            return HtmlInstrumentation::fromConfig($config);
        }

        if (isset($this->configured[$key])) {
            return $this->configured[$key];
        }

        foreach (array_keys($this->configured) as $configured) {
            if (str_starts_with($configured, 'html:')) {
                unset($this->configured[$configured]);
            }
        }

        return $this->configured[$key] = HtmlInstrumentation::fromConfig($config);
    }

    /** @param  array<string, mixed>  $config */
    protected function configurationKey(array $config): ?string
    {
        $fingerprint = HtmlInstrumentation::configurationFingerprint($config);

        return $fingerprint === null ? null : 'html:'.$fingerprint;
    }

    /** @param  string[]|null  $engines */
    public function forget(object|string $piece, ?array $engines = null): static
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

        if ($wantsAntlers && in_array($piece, $this->antlersPieces, true)) {
            $this->antlersPieces = $this->without($this->antlersPieces, $piece);
            $this->htmlInstrumenters = null;

            $this->removeFromAntlers($piece);
        }

        return $this;
    }

    public function flush(): static
    {
        $antlers = $this->antlersPieces;
        $preparsers = $this->runtimePreparsers;
        $visitors = $this->runtimeVisitors;

        $this->configured = [];
        $this->antlersPieces = [];
        $this->runtimePreparsers = [];
        $this->runtimeVisitors = [];
        $this->htmlInstrumenters = null;
        $this->cleanedWith = [];
        $this->cleanedHash = null;

        foreach ($antlers as $piece) {
            $this->removeFromAntlers($piece);
        }

        if ($visitors !== []) {
            RuntimeParser::clearRenderNodeCache();
        }

        $this->applyToAntlers(function (RuntimeConfiguration $config) use ($preparsers, $visitors) {
            foreach ($preparsers as $preparser) {
                $config->removePreparser($preparser);
            }

            foreach ($visitors as $visitor) {
                $config->removeVisitor($visitor);
            }
        });

        return $this;
    }

    protected function removeFromAntlers(object $piece): void
    {
        $this->applyToAntlers(function (RuntimeConfiguration $config) use ($piece) {
            if ($piece instanceof HtmlInstrumentation) {
                $config->removePreparser($piece);

                return;
            }

            $config->traceManager?->removeTracer($piece);
        }, ! $piece instanceof HtmlInstrumentation);
    }

    protected function rememberAntlersPiece(HtmlInstrumentation|RuntimeTracerContract|TracerContract $piece): void
    {
        if (! in_array($piece, $this->antlersPieces, true)) {
            $this->antlersPieces[] = $piece;
            $this->htmlInstrumenters = null;
        }
    }

    /**
     * @template T
     *
     * @param  T[]  $items
     * @return T[]
     */
    protected function without(array $items, mixed $needle): array
    {
        return array_values(array_filter($items, fn ($item) => $item !== $needle));
    }

    public function applyTo(RuntimeConfiguration $runtimeConfiguration): void
    {
        $this->runtimeConfigurations[$runtimeConfiguration] ??= $runtimeConfiguration->isTracingEnabled;

        if (($configured = $this->configuredInstrumenter()) !== null) {
            $runtimeConfiguration->preparse($configured);
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

    public function sync(): static
    {
        $this->configuredInstrumenter();

        return $this;
    }

    public function instrumenting(): bool
    {
        return $this->htmlInstrumenters() !== [];
    }

    public function removeMarkers(string $html): string
    {
        return HtmlInstrumentation::removeMarkers($html, $this->htmlInstrumenters());
    }

    public function containsMarkers(string $html): bool
    {
        return HtmlInstrumentation::containsMarkers($html, $this->htmlInstrumenters());
    }

    public function tagBody(AntlersNode $node, bool $composesOutput = false): TagBody
    {
        $instrumenters = $this->htmlInstrumenters();
        $key = $this->tagBodyKey($instrumenters, $composesOutput);

        if ($node->tagBody !== null && $node->tagBody[0] === $key) {
            return $node->tagBody[1];
        }

        $body = TagBody::from((string) $node->runtimeContent, $instrumenters, $composesOutput);
        $node->tagBody = [$key, $body];

        return $body;
    }

    /** @param  HtmlInstrumentation[]  $instrumenters */
    protected function tagBodyKey(array $instrumenters, bool $composesOutput): string
    {
        $mode = $composesOutput ? 'composes' : 'clean';

        return $mode.':'.implode("\0", $this->fingerprints($instrumenters));
    }

    /** @param  HtmlInstrumentation[]|null  $instrumenters */
    public function removeMisplacedMarkers(string $html, ?array $instrumenters = null): string
    {
        $instrumenters ??= $this->htmlInstrumenters();

        if ($instrumenters === []) {
            return $html;
        }

        if (strlen($html) === $this->cleanedLength
            && $this->cleanedCovers($instrumenters)
            && hash('xxh3', $html) === $this->cleanedHash) {
            return $html;
        }

        $cleaned = $this->cleanMisplacedMarkers($html, $instrumenters);

        if ($cleaned !== $html) {
            $this->misplacedMarkerRemovals++;
            $this->removedMisplacedMarkerBytes += strlen($html) - strlen($cleaned);
        }

        $this->cleanedWith = $this->fingerprints($instrumenters);
        $this->cleanedLength = strlen($cleaned);
        $this->cleanedHash = hash('xxh3', $cleaned);

        return $cleaned;
    }

    public function misplacedMarkerRemovals(): int
    {
        return $this->misplacedMarkerRemovals;
    }

    public function removedMisplacedMarkerBytes(): int
    {
        return $this->removedMisplacedMarkerBytes;
    }

    /** @param  HtmlInstrumentation[]  $instrumenters */
    protected function cleanedCovers(array $instrumenters): bool
    {
        return array_diff($this->fingerprints($instrumenters), $this->cleanedWith) === [];
    }

    /**
     * @param  HtmlInstrumentation[]  $instrumenters
     * @return string[]
     */
    protected function fingerprints(array $instrumenters): array
    {
        return array_map(
            fn (HtmlInstrumentation $instrumenter) => $instrumenter->cacheFingerprint(),
            $instrumenters
        );
    }

    /** @param  HtmlInstrumentation[]  $instrumenters */
    protected function cleanMisplacedMarkers(string $html, array $instrumenters): string
    {
        return HtmlInstrumentation::removeMisplacedMarkers($html, $instrumenters);
    }

    public function trimAroundMarkers(string $html): string
    {
        return HtmlInstrumentation::trimAroundMarkers($html, $this->htmlInstrumenters());
    }

    /** @return array{0: string, 1: string|null} */
    public function splitMarkers(string $output): array
    {
        $instrumenters = $this->htmlInstrumenters();

        if ($instrumenters === [] || ! HtmlInstrumentation::containsMarkers($output, $instrumenters)) {
            return [trim($output), null];
        }

        $instrumented = HtmlInstrumentation::trimAroundMarkers($output, $instrumenters);
        $stripped = HtmlInstrumentation::removeMarkers($instrumented, $instrumenters);
        $value = trim($stripped);

        if ($instrumented === $value || $stripped !== $value) {
            return [$value, null];
        }

        return [$value, $instrumented];
    }

    /** @return HtmlInstrumentation[] */
    protected function htmlInstrumenters(): array
    {
        $configured = $this->configuredInstrumenter();

        if ($this->htmlInstrumenters !== null) {
            return $this->htmlInstrumenters;
        }

        $instrumenters = array_values(array_filter(
            $this->antlersPieces,
            fn ($piece) => $piece instanceof HtmlInstrumentation
        ));

        if ($configured !== null) {
            $instrumenters[] = $configured;
        }

        return $this->htmlInstrumenters = $instrumenters;
    }

    protected function configuredInstrumenter(): ?HtmlInstrumentation
    {
        $config = Config::all()['statamic']['antlers']['instrumentation'] ?? [];

        if ($this->configuredState !== null && $this->configuredState[0] === $config) {
            return $this->configuredState[1];
        }

        $instrumenter = null;

        if (is_array($config) && ($config['enabled'] ?? false)) {
            $instrumenter = $this->configuredHtmlInstrumentation(
                array_intersect_key($config, array_flip(['comments', 'prefix', 'attributes', 'metadata', 'componentPrefixes', 'cache']))
            );
        }

        $previous = $this->configuredState[1] ?? null;
        $this->configuredState = [$config, $instrumenter];

        if ($previous !== $instrumenter) {
            $this->htmlInstrumenters = null;

            $this->applyToAntlers(
                fn (RuntimeConfiguration $runtimeConfiguration) => $runtimeConfiguration->replacePreparser($previous, $instrumenter)
            );
        }

        return $instrumenter;
    }

    /** @param  string[]|null  $engines */
    protected function registerTracer(RuntimeTracerContract|TracerContract $tracer, ?array $engines): static
    {
        $engines ??= [static::ENGINE_ANTLERS];

        if (in_array(static::ENGINE_ANTLERS, $engines, true)) {
            $this->rememberAntlersPiece($tracer);

            $this->applyToAntlers(function (RuntimeConfiguration $config) use ($tracer) {
                $config->enableTracing()->registerTracer($tracer);
            }, true);
        }

        return $this;
    }

    /** @param  string[]|null  $engines */
    protected function registerHtmlInstrumentation(HtmlInstrumentation $instrumentation, ?array $engines): static
    {
        $wantsAntlers = $engines === null || in_array(static::ENGINE_ANTLERS, $engines, true);

        if ($wantsAntlers) {
            $this->rememberAntlersPiece($instrumentation);
            $this->applyToAntlers(fn (RuntimeConfiguration $config) => $config->preparse($instrumentation));
        }

        return $this;
    }

    protected function applyToAntlers(callable $apply, bool $syncTracing = false): void
    {
        foreach ($this->runtimeConfigurations as $configuration => $tracingEnabled) {
            $apply($configuration);

            if ($syncTracing) {
                $configuration->isTracingEnabled = $tracingEnabled || $configuration->hasRegisteredTracers();
            }
        }
    }

    /**
     * @param  string[]|null  $engines
     * @return string[]|null
     */
    protected function normalizeEngines(?array $engines): ?array
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
