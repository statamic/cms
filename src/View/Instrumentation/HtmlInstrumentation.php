<?php

namespace Statamic\View\Instrumentation;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\Antlers\TemplateAnalyzer as AntlersTemplateAnalyzer;

/** @internal */
class HtmlInstrumentation
{
    use Concerns\ConfiguresInstrumentation,
        Concerns\EmitsInstrumentation,
        Concerns\MemoizesResults;

    const SKIP_FILTERED = 'filtered';

    const SKIP_INSIDE_HTML_COMMENT = 'inside-html-comment';

    const SKIP_INSIDE_DOCTYPE = 'inside-doctype';

    const SKIP_DYNAMIC_MARKUP = 'dynamic-markup';

    const SKIP_CLOSING_TAG_MARKUP = 'closing-tag-markup';

    const SKIP_CROSS_CONTAINER_PAIR = 'cross-container-pair';

    const SKIP_NO_STRATEGY = 'no-available-strategy';

    /** @var bool */
    protected $commentsEnabled = true;

    /** @var string */
    protected $commentPrefix = 'antlers';

    /** @var callable|null */
    protected $commentFactory = null;

    /** @var array<string, callable> */
    protected $attributeLayers = [];

    /** @var array<string, mixed> */
    protected $extraMetadata = [];

    /** @var callable|null */
    protected $nodeFilter = null;

    /** @var callable|null */
    protected $skipCallback = null;

    /** @var string[] */
    protected $componentPrefixes = ComponentPrefixes::DEFAULTS;

    /** @var array<string, string> */
    protected $resultCache = [];

    /** @var array<string, array<string, string>> */
    protected static $sharedResultCache = [];

    /** @var string|null */
    protected $cacheScope = null;

    /** @var int */
    protected $resultCacheLimit = 256;

    protected ?string $instanceFingerprint = null;

    protected int $configurationRevision = 0;

    protected ?array $collectedEdits = null;

    public static function instrumentTogether(string $template, array $instrumenters): string
    {
        if ($instrumenters === []) {
            return $template;
        }

        if (InstrumentationState::parsingComponentContent() || InstrumentationState::suppressesHtmlInstrumentation()) {
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

        if (($cached = $instrumenters[0]->cachedResult($cacheKey)) !== null) {
            return $cached;
        }

        $edits = [];
        $attributes = [];

        foreach ($instrumenters as $index => $instrumenter) {
            $previous = $instrumenter->collectedEdits;
            $instrumenter->collectedEdits = [];

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

        return $instrumenters[0]->storeResult($cacheKey, $instrumenters[0]->spliceByteEdits($template, $edits));
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

    /** @return static */
    public static function make()
    {
        return new static;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return self
     */
    public static function fromConfig(array $config)
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

        $instrumentation->cacheScope = static::configurationFingerprint($config);

        return $instrumentation;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return string|null
     */
    public static function configurationFingerprint(array $config)
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

    /**
     * @param  string  $template
     * @return string
     */
    public function instrument($template)
    {
        return $this->instrumentAntlers($template);
    }

    /**
     * @param  string  $template
     * @return string
     */
    public function instrumentAntlers($template)
    {
        if (InstrumentationState::parsingComponentContent() || InstrumentationState::suppressesHtmlInstrumentation()) {
            return $template;
        }

        if ((! $this->commentsEnabled && empty($this->attributeLayers))
            || strpos($template, '{{') === false) {
            return $template;
        }

        $view = GlobalRuntimeState::$currentExecutionFile;

        $commentsAtDocumentRoot = ! InstrumentationState::renderingComponentView()
            && ! InstrumentationState::instrumentingView();

        $cacheKey = $this->resultCacheKey(
            $template,
            $view,
            $commentsAtDocumentRoot
        );
        $cached = null;

        if ($this->collectedEdits === null) {
            $cached = $this->cachedResult($cacheKey);
        }

        if ($cached !== null) {
            return $cached;
        }

        $analysis = (new AntlersTemplateAnalyzer($this->componentPrefixes))->analyze(
            $template,
            ! empty($this->attributeLayers),
            $commentsAtDocumentRoot,
            InstrumentationState::sourceMap()
        );

        $instrumented = $this->emitInstrumentation($template, $analysis);

        if ($this->collectedEdits !== null) {
            return $instrumented;
        }

        return $this->storeResult($cacheKey, $instrumented);
    }

    /**
     * @param  string  $template
     * @return string
     */
    public function __invoke($template)
    {
        return InstrumentationState::whileInstrumentingView(
            fn () => $this->instrumentAntlers($template)
        );
    }

    /**
     * @param  string  $marker
     * @return array<string, mixed>|null
     */
    public static function decode($marker)
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

    protected function resultCacheKey($template, $view, $commentsAtDocumentRoot)
    {
        $parts = [InstrumentationState::sourceMap()?->fingerprint()];

        $documentContext = 'nested';

        if ($commentsAtDocumentRoot) {
            $documentContext = 'root';
        }

        $viewPath = '';

        if (is_string($view)) {
            $viewPath = $view;
        }

        $parts[] = $documentContext;
        $parts[] = $viewPath;
        $parts[] = $template;

        return hash('xxh128', serialize($parts));
    }
}
