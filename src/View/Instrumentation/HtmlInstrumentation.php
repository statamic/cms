<?php

namespace Statamic\View\Instrumentation;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\Antlers\TemplateAnalyzer as AntlersTemplateAnalyzer;
use Throwable;

/** @internal */
class HtmlInstrumentation
{
    use Concerns\ConfiguresInstrumentation,
        Concerns\EmitsInstrumentation,
        Concerns\MemoizesResults,
        Concerns\RemovesMisplacedMarkers;

    const SKIP_FILTERED = 'filtered';

    const SKIP_INSIDE_HTML_COMMENT = 'inside-html-comment';

    const SKIP_INSIDE_DOCTYPE = 'inside-doctype';

    const SKIP_INSIDE_PHP = 'inside-php';

    const SKIP_DYNAMIC_MARKUP = 'dynamic-markup';

    const SKIP_CLOSING_TAG_MARKUP = 'closing-tag-markup';

    const SKIP_INSIDE_CHARACTER_REFERENCE = 'inside-character-reference';

    const SKIP_CROSS_CONTAINER_PAIR = 'cross-container-pair';

    const SKIP_NO_STRATEGY = 'no-available-strategy';

    const CACHE_FORMAT = 1;

    const CACHE_TTL = 2592000;

    protected bool $commentsEnabled = true;

    protected string $commentPrefix = 'antlers';

    protected ?Closure $commentFactory = null;

    /** @var array<string, Closure> */
    protected array $attributeLayers = [];

    /** @var array<string, mixed> */
    protected array $extraMetadata = [];

    protected ?Closure $nodeFilter = null;

    protected ?Closure $skipCallback = null;

    /** @var string[] */
    protected array $componentPrefixes = ComponentPrefixes::DEFAULTS;

    /** @var array<string, array{0: string, 1: array<int, HtmlContext|null>|null, 2: string}> */
    protected static array $results = [];

    protected static int $resultLimit = 256;

    /** @var array<string, array<int, HtmlContext|null>> */
    protected static array $annotations = [];

    protected static int $annotationLimit = 256;

    protected static bool $transfersContexts = true;

    protected static ?string $cacheStoreNamespace = null;

    protected ?string $cacheScope = null;

    protected bool|string $cacheStore = false;

    protected bool $cacheStoreFailed = false;

    protected ?Throwable $lastCacheStoreException = null;

    protected ?string $instanceFingerprint = null;

    protected int $configurationRevision = 0;

    protected ?array $collectedEdits = null;

    /** @var array<int, HtmlContext|null>|null */
    protected ?array $lastContexts = null;

    public static function instrumentTogether(string $template, array $instrumenters): string
    {
        if ($instrumenters === []) {
            return $template;
        }

        if (InstrumentationState::suppressed()) {
            return $template;
        }

        $invoke = fn ($instrumenter) => $instrumenter($template);

        if (count($instrumenters) === 1) {
            return $invoke($instrumenters[0]);
        }

        $cacheKey = 'group:'.hash('xxh128', serialize(array_map(fn ($instrumenter) => $instrumenter->cacheFingerprint(), $instrumenters)))
            .$instrumenters[0]->resultCacheKey(
                $template,
                GlobalRuntimeState::$currentExecutionFile,
                false
            );

        $reportsSkips = false;
        $persistent = GlobalRuntimeState::$currentExecutionFile !== null && InstrumentationState::attributesView();

        foreach ($instrumenters as $instrumenter) {
            if ($instrumenter->skipCallback !== null) {
                $reportsSkips = true;
            }

            if ($instrumenter->cacheScope === null) {
                $persistent = false;
            }
        }

        if (! $reportsSkips && ($cached = $instrumenters[0]->cachedResult($cacheKey, $persistent)) !== null) {
            return $cached;
        }

        $edits = [];
        $attributes = [];

        foreach ($instrumenters as $index => $instrumenter) {
            $previous = $instrumenter->collectedEdits;
            $instrumenter->collectedEdits = [];
            $instrumenter->lastContexts = null;

            try {
                $invoke($instrumenter);

                foreach ($instrumenter->collectedEdits as $edit) {
                    if (isset($edit['attributeName'])) {
                        $key = $edit['start'].':'.strtolower($edit['attributeName']);

                        if (isset($attributes[$key])) {
                            continue;
                        }

                        $attributes[$key] = true;
                    }

                    $edit['instrumenter'] = $index;
                    $edits[] = $edit;
                }
            } finally {
                $instrumenter->collectedEdits = $previous;
            }
        }

        return $instrumenters[0]->storeResult(
            $cacheKey,
            $instrumenters[0]->spliceByteEdits($template, $edits),
            $instrumenters[0]->lastContexts,
            $persistent && ! $reportsSkips
        );
    }

    /** @return string[] */
    public function getComponentPrefixes(): array
    {
        return $this->componentPrefixes;
    }

    public function cacheFingerprint(): string
    {
        if ($this->cacheScope !== null) {
            return 'config:'.$this->cacheScope;
        }

        $this->instanceFingerprint ??= bin2hex(random_bytes(16));

        return $this->instanceFingerprint.':'.$this->configurationRevision;
    }

    final protected function __construct()
    {
    }

    public static function make(): static
    {
        return new static;
    }

    /** @param  array<string, mixed>  $config */
    public static function fromConfig(array $config): static
    {
        $instrumentation = static::make();

        if (array_key_exists('comments', $config) && ! $config['comments']) {
            $instrumentation->withoutComments();
        } else {
            $instrumentation->comments($config['prefix'] ?? 'antlers');
        }

        foreach ((array) ($config['attributes'] ?? []) as $attributeName) {
            if (is_string($attributeName) && $attributeName !== '') {
                $instrumentation->attributes($attributeName);
            }
        }

        if (is_array($config['metadata'] ?? null)) {
            $instrumentation->withMetadata($config['metadata']);
        }

        if (is_array($config['componentPrefixes'] ?? null)) {
            $instrumentation->componentPrefixes($config['componentPrefixes']);
        }

        $cache = array_key_exists('cache', $config) ? $config['cache'] : true;
        $instrumentation->cacheStore = is_string($cache) && $cache !== '' ? $cache : (bool) $cache;
        $instrumentation->cacheScope = static::configurationFingerprint($config);

        return $instrumentation;
    }

    /** @param  array<string, mixed>  $config */
    public static function configurationFingerprint(array $config): ?string
    {
        $encoded = json_encode(
            $config,
            JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR
        );

        if ($encoded === false) {
            return null;
        }

        return md5($encoded);
    }

    public function instrument(string $template): string
    {
        return $this->instrumentAntlers($template);
    }

    public function instrumentAntlers(string $template): string
    {
        $this->lastContexts = null;

        if (InstrumentationState::suppressed()) {
            return $template;
        }

        if ((! $this->commentsEnabled && empty($this->attributeLayers))
            || strpos($template, '{{') === false) {
            return $template;
        }

        if ($this->commentsEnabled
            && $this->commentFactory === null
            && str_contains($template, '<!-- '.$this->commentPrefix.':start ')) {
            return $template;
        }

        $view = GlobalRuntimeState::$currentExecutionFile;

        $commentsAtDocumentRoot = ! InstrumentationState::inComponentView()
            && ! InstrumentationState::instrumenting();

        $cacheKey = $this->resultCacheKey(
            $template,
            $view,
            $commentsAtDocumentRoot
        );
        $memoizable = $this->collectedEdits === null && $this->skipCallback === null;
        $persistent = $memoizable && $view !== null && InstrumentationState::attributesView();

        if ($memoizable && ($cached = $this->cachedResult($cacheKey, $persistent)) !== null) {
            return $cached;
        }

        $analysis = (new AntlersTemplateAnalyzer($this->componentPrefixes))->analyze(
            $template,
            ! empty($this->attributeLayers),
            $commentsAtDocumentRoot,
            InstrumentationState::sourceMap()
        );

        $this->lastContexts = $analysis['contexts'];
        $instrumented = $this->emitInstrumentation($template, $analysis);

        if ($this->collectedEdits !== null) {
            return $instrumented;
        }

        return $this->storeResult($cacheKey, $instrumented, $analysis['contexts'], $persistent);
    }

    public function __invoke(string $template): string
    {
        return InstrumentationState::whileInstrumenting(
            fn () => $this->instrumentAntlers($template)
        );
    }

    /** @return Collection<int, array<string, mixed>> */
    public function regions(string $html): Collection
    {
        return static::regionsIn($html, [$this]);
    }

    /**
     * @param  HtmlInstrumentation[]  $instrumenters
     * @return Collection<int, array<string, mixed>>
     */
    public static function regionsIn(string $html, array $instrumenters): Collection
    {
        [, $markers] = static::extractMarkers($html, $instrumenters);

        return collect($markers)->flatMap(function (array $marker) {
            $text = $marker[1];

            if (str_starts_with($text, '<!-- ')) {
                return str_contains($text, ':start ') ? array_filter([static::decode($text)]) : [];
            }

            $value = htmlspecialchars_decode(substr($text, strpos($text, '="') + 2, -1), ENT_QUOTES);
            $regions = static::decode($value) ?? json_decode($value, true);

            return is_array($regions) && array_is_list($regions) ? array_filter($regions, 'is_array') : [];
        })->values();
    }

    /** @return array<string, mixed>|null */
    public static function decode(string $marker): ?array
    {
        if (str_contains($marker, '<!-- ')) {
            $marker = Str::betweenFirst($marker, ':start ', ' -->');
        }

        $decoded = base64_decode($marker, true);

        if ($decoded === false) {
            return null;
        }

        $metadata = json_decode($decoded, true);

        if (! is_array($metadata)) {
            return null;
        }

        return $metadata;
    }

    protected function resultCacheKey(string $template, ?string $view, bool $commentsAtDocumentRoot): string
    {
        return ($commentsAtDocumentRoot ? 'root:' : 'nested:')
            .InstrumentationState::lineSeed()
            .':'.InstrumentationState::offsetSeed()
            .':'.(InstrumentationState::attributesView() ? '1' : '0')
            .':'.(InstrumentationState::sourceMap()?->fingerprint() ?? '')
            .':'.($view ?? '')
            .':'.hash('xxh128', $template);
    }
}
