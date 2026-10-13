# Antlers HTML instrumentation: usage, recipes and limitations

Review companion for `feat/antlers-html-instrumentation` as of 2026-10-03. Everything below reflects the working tree, not a published API. Sections marked *internal* describe machinery an addon should not touch. Note that every class in sections 2–5 (`HtmlInstrumentation`, `InstrumentationManager`, `Span`, `TracerContract`) is currently tagged `@internal` too; only `ComposesRenderedOutput` is unmarked. Whether those become public API is an open decision.

## 1. What it does

When instrumentation is enabled, every rendered Antlers region is wrapped in HTML comment markers (and, optionally, the owning element receives a data attribute) carrying the region's source location. Markers are placed only where HTML allows a comment or an attribute; regions inside `<script>`, `<textarea>`, attribute values, doctype, PHP blocks and character references are skipped, and a final pass removes any marker that still ended up somewhere unsafe.

The invariant the runtime enforces: **markers never change template logic**. Any rendered text that becomes a *value* (a variable, a slot, a modifier input, a comparison operand, a tag's string result) is marker-free. Markers only exist on the print path.

```html
<!-- source -->
<div>{{ title }}</div>

<!-- rendered with comments -->
<div><!-- antlers:start eyJ0eXBlIjoic2luZ2xl... -->Hello<!-- antlers:end 1 --></div>

<!-- rendered with attributes -->
<div data-antlers="W3sidHlwZSI6InNpbmdsZSIs...">Hello</div>
```

## 2. Enabling it

### Configuration (`config/antlers.php`)

```php
'instrumentation' => [
    'enabled' => env('STATAMIC_ANTLERS_INSTRUMENTATION', false),
    'cache' => env('STATAMIC_ANTLERS_INSTRUMENTATION_CACHE', true), // true, a store name, or false
    'comments' => true,           // wrap regions in <!-- prefix:start ... --> / <!-- prefix:end n -->
    'prefix' => 'antlers',        // comment prefix
    'attributes' => [],           // attribute names to add to owning elements, e.g. ['data-antlers']
    'metadata' => [],             // extra keys merged into every marker payload
    'componentPrefixes' => [],    // extra tag prefixes treated as components (defaults: x- x: s- s: statamic- statamic:)
],

'tracing' => env('STATAMIC_ANTLERS_TRACING', false),
'tracers' => [],                  // class names resolved from the container
```

Analysis results are cached by template content and Statamic version; `php artisan cache:clear` drops them. The configuration is re-read at the start of each outermost render; when it changes, the manager swaps the configured instrumenter on every tracked runtime configuration.

### Programmatically, through the `Runtime` facade

`Statamic\View\Antlers\Language\Facades\Runtime` resolves to the `InstrumentationManager` singleton. Core code uses it for everything except the places that receive the manager by injection (`RuntimeParser`, `RemoveMisplacedMarkersFromResponse`).

```php
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Instrumentation\HtmlInstrumentation;

$instrumenter = HtmlInstrumentation::make()
    ->comments('trace')                 // or ->withoutComments()
    ->attributes('data-trace')          // base64 JSON array of the element's regions; several names allowed
    ->jsonAttribute('data-trace-json')  // same payload, plain JSON
    ->attribute('data-first', fn (array $regions) => $regions[0]['expression'])
    ->only('title', 'partial:*')        // only these expressions get markers
    ->except('partial:nav*')
    ->filter(fn (array $metadata, $region) => $metadata['line'] > 10)
    ->onSkip(fn (array $metadata, $region, string $reason) => logger($reason))
    ->componentPrefixes('ui:', 'flux:')
    ->markersUsing(fn (array $metadata) => ['<!--s-->', '<!--e-->']); // custom markers, see limitations

$html = Runtime::using($instrumenter, fn () => view('home')->render()); // registered for the callback only

Runtime::register($instrumenter);            // engines default to ['antlers']
Runtime::register(MyTracer::class);          // resolved from the container
Runtime::forget($instrumenter);
Runtime::flush();                            // every registered piece, preparser and visitor; the config-driven instrumenter stays while the config enables it
Runtime::preparse($callable);                // process-wide preparser; Runtime::removePreparser($callable) withdraws it
Runtime::addVisitor($visitor);               // process-wide node visitor; Runtime::removeVisitor($visitor) withdraws it
```

`only`, `except` and `filter` stack: a region gets markers when it passes all of them. Several instrumenters can be registered at once; they are applied together in one analysis pass.

### Helpers on the same facade

```php
Runtime::instrumenting();                        // any HTML instrumenter registered?
Runtime::regions($html);                         // Collection of decoded regions, see section 4
Runtime::containsMarkers($html);                 // fast check, no regex
Runtime::removeMarkers($html);                   // strip comment and attribute markers
Runtime::removeMisplacedMarkers($html);          // the final-pass cleaner, remembers the last cleaned result
Runtime::trimAroundMarkers($html);               // trim() that reaches through edge markers
Runtime::splitMarkers($output);                  // [$clean, $instrumentedTwin|null]
Runtime::tagBody($node, $composesOutput);        // internal, see section 6
```

## 3. Instrumenting a template or a string

Registering an instrumenter (or enabling the config) changes nothing by itself; markers appear when Antlers renders something through one of the paths below. Which path you use decides whether you get markers at all, and what `view`/`line`/`start` mean.

| Entry point | Markers | `view` | Offsets relative to |
|---|---|---|---|
| `view('page', $data)->render()` | yes | `resources/views/page.antlers.html` | the file, front matter included |
| `Antlers::parse($source, $data, trusted: true)` | yes | absent | the string (line 1) |
| `Antlers::parse($source, $data)` (untrusted, the default) | never | | |
| `Antlers::parser()->parse($source, $data)` outside a view render | never | | |
| `Antlers::parse(..., instrument: false)` / `RuntimeParser::CAPTURED_CONTENT` | never | | |
| Blade `@antlers ... @endantlers` | yes | `storage/framework/views/antlers_<sha1>.antlers.html` | the extracted block, not the Blade file |
| `$instrumenter->instrument($source)` (no render) | yes, on the source | absent | the string |

### A view

```php
$instrumenter = HtmlInstrumentation::make();

$html = Runtime::using($instrumenter, fn () => view('pages.show', ['title' => 'Hello'])->render());
// <div><!-- antlers:start ... -->Hello<!-- antlers:end 1 --></div>

$instrumenter->regions($html)->first();
// ['expression' => 'title', 'view' => 'resources/views/pages/show.antlers.html', 'line' => 4, 'start' => 22, ...]
```

Prefer `using` over `register`: a registered instrumenter stays active for every later render in the process (queue workers, Octane, tests). `using` forgets it afterwards, even when the callback throws, and leaves an instrumenter that was already registered in place.

### An arbitrary string, rendered

```php
$output = Runtime::using($instrumenter, fn () => Antlers::parse('<ul>{{ items }}<li>{{ name }}</li>{{ /items }}</ul>', $data, trusted: true));

(string) $output;          // with markers when called outside a tag
$output->instrumented();   // with markers, explicitly
Runtime::removeMarkers((string) $output); // clean
```

- `trusted: true` is required. Untrusted strings are user content and render under suppression, so they never carry markers.
- `Antlers::parser()->parse()` called outside a view render is also treated as user data (`GlobalRuntimeState::$isEvaluatingUserData` defaults to `true`), so it never gets markers. Inside a view render it does.
- There is no `view`; offsets count from the start of the string. Use `withMetadata(['source' => ...])` on a dedicated instrumenter if the consumer needs to know where the string came from.
- A string with no element around a region (`'{{ title }}'`, `'Hi {{ name }}'`) gets no comment markers; see the root-level limitation.
- Inside a tag, `(string)` on the result is marker-free. Return the object to keep the markers.

### An arbitrary string, without rendering

`instrument()` works on Antlers *source*: it wraps each region of the template in markers and returns the template, unrendered. Nothing needs to be registered, and nothing is evaluated.

```php
$source = HtmlInstrumentation::make()->instrument("Intro {{ title }}\n<p>{{ title }}</p>");
// Intro <!-- antlers:start ... -->{{ title }}<!-- antlers:end 1 -->
// <p><!-- antlers:start ... -->{{ title }}<!-- antlers:end 2 --></p>
```

Use this for tooling that only needs the region map (editors, linters, source maps). Two differences from the runtime path:

- It marks root-level regions; the runtime (`__invoke`) does not.
- Its output is not meant to be rendered. Rendering it with no instrumenter registered prints the markers as literal comments and value paths (slots, modifiers, comparisons) do not strip them, the same problem as a parser-local instrumenter. Rendering it with the same instrumenter registered is a no-op instrumentation-wise: text that already contains `<!-- prefix:start ` is returned unchanged.

### In tests

`FakesViews::viewShouldReturnRaw()` has no file on disk, so there is no source seed: offsets count from the start of the template *body*, front matter excluded. Write a real file (`view()->addLocation($dir)`) when the assertion is about file offsets.

## 4. Reading markers

```php
$instrumenter->regions($html);    // markers written by this instrumenter
Runtime::regions($html);          // markers written by every registered or configured instrumenter

$instrumenter->regions($html)->where('expression', 'title')->pluck('line');
HtmlInstrumentation::decode($marker); // one comment marker or attribute payload
```

`regions()` returns a Collection with one entry per marker occurrence, in document order. A region rendered twice (a loop) appears twice, and so does a region carried by both a comment and an attribute layer. Attribute values that are not base64 or plain JSON (a custom `attribute()` factory) are skipped.

Each decoded region is an array with:

| Key | Meaning |
|---|---|
| `type` | `single` or `pair` |
| `engine` | `antlers` |
| `expression` | the expression text, e.g. `title`, `partial:card`, `if x` |
| `line`, `start`, `end` | file-relative line and character offsets, front matter included |
| `context` | `element-content`, `raw-text`, `attribute-value`, ... |
| `element` | owning element name, when any |
| `attribute` | attribute name, when the region sits in an attribute value |
| `view` | the view path relative to `base_path()` (only the file name when the view lives outside it); absent for string templates |
| `id` | per-document counter that pairs the `end` marker |
| anything from `metadata` config | merged verbatim |

Skip reasons passed to `onSkip` are the `HtmlInstrumentation::SKIP_*` constants: `filtered`, `inside-html-comment`, `inside-doctype`, `inside-php`, `dynamic-markup`, `closing-tag-markup`, `inside-character-reference`, `cross-container-pair`, `no-available-strategy`.

## 5. Tracing (spans)

Independent of HTML markers, tracers observe node evaluation.

```php
use Statamic\View\Instrumentation\Span;
use Statamic\View\Instrumentation\TracerContract;

class MyTracer implements TracerContract
{
    public function onEnter(Span $span): mixed
    {
        $node = $span->raw();            // the AntlersNode
        $span->expression;               // also ->engine, ->kind, ->view, ->line
        $span->meta('processor_depth');  // or the whole meta array
        $span->scope();                  // active data, when available

        return microtime(true);          // handle passed back to onExit
    }

    public function onExit(Span $span, mixed $handle, mixed $output): void {}

    public function onRenderComplete(): void {}
}

Runtime::register(new MyTracer);
```

The legacy `RuntimeTracerContract` (`onEnter(AbstractNode)`, `onExit(AbstractNode, $content)`, `onRenderComplete()`) is still accepted and is what the debugbar profiler uses. Render completion fires once per outermost render, process-wide.

## 6. How tag bodies are handled

This is the part that changed most recently and the part to review hardest.

### The rule

A tag never sees markers by default.

- `$this->content` is the marker-free body.
- `(string) $this->parse()` and `(string) $this->parseLoop(...)` are marker-free.
- Returning the render object untouched prints the inner markers with their authored offsets.

```php
class Cards extends Tags
{
    public function index()
    {
        return $this->parseLoop($this->items());      // inner markers kept
    }

    public function excerpt()
    {
        return Str::limit((string) $this->parse(), 80); // marker-free, as the string suggests
    }

    public function wrapped()
    {
        return '<section>'.$this->parse().'</section>'; // marker-free: the render was read as a string
    }
}
```

### Opting a tag in

A tag that composes output around its render and wants the markers to survive implements the marker interface. It then receives the body and its renders with markers included, exactly as every tag did before this change.

```php
use Statamic\Contracts\View\Instrumentation\ComposesRenderedOutput;

class Wrapper extends Tags implements ComposesRenderedOutput
{
    public function index()
    {
        return '<section>'.$this->parse().'</section>'; // inner markers kept
    }
}
```

Core implementers: `Statamic\Forms\Tags` (the form tag concatenates its body) and `Statamic\StaticCaching\NoCache\Tags` (stores the body source for later rendering).

### Why bodies keep their authored offsets (internal)

`TagBody` splits the instrumented body into clean text plus a marker map. When a tag parses text derived from the clean body, the runtime aligns it back: the exact body, a trimmed or sliced body (breadcrumbs trim their body), or the body wrapped in more template (`{{ fields }}` wraps its body) all map back to the instrumented text, so offsets stay file-relative and nothing is instrumented twice. Text unrelated to the body is treated as a fresh string template and instrumented with line 1 offsets and no `view`.

The alignment scope is pushed and popped around the tag call in the processor (`InstrumentationState::withTagBody`), so a tag that throws cannot leave a stale body behind. Lazy loops capture the scope when created and restore it while rendering.

### Conditions, modifiers and interpolations

Every `{...}` interpolation, whether inside a condition, a modifier chain, a modifier or tag parameter, an assignment, a `switch`, a ternary or `??`, is reduced by a separate interpolation processor that renders under suppression and strips markers before the value is used. So `{{ if {partial:thing} | length > 5 }}` compares the clean length, `{{ {partial:thing} | upper }}` uppercases clean text, and `{{ x = {partial:thing} }}{{ x }}` prints clean text. Eighteen such shapes are verified neutral (off output equals on output with markers removed); the neutrality test file pins the representative ones. The marker's `expression` for these regions is the authored text, interpolations included, not the parser's internal `int_…` placeholders.

### Slots

Partial and include slots are values, so `{{ slot }}`, `{{ slot | length }}` and `{{ if slot == ... }}` see clean strings. The instrumented twin travels alongside in the view data (`InstrumentationState::twinsKey()`) and is printed only for a bare `{{ slot }}` / `{{ slot:name }}` in a comment-safe context. `Statamic\View\Slot` objects follow the same split: `render()` prints markers, `__toString`, `toHtml` and serialisation are marker-free.

## 7. Parsing captured text

Text that was captured from an earlier instrumented render must not be instrumented again. Pass the flag rather than relying on state:

```php
Antlers::parse($source, $data, trusted: true, instrument: false);
```

Views rendered through Laravel's view factory carry it as a reserved data key, which is what the nocache fragment does:

```php
view('nocache::'.$name, $data + [RuntimeParser::CAPTURED_CONTENT => true])->render();
```

## 8. Suppression (internal)

`InstrumentationState::withoutInstrumentation(fn () => ...)` renders without adding markers, deeply, including partials rendered inside. It is used where output placement is unknown or where the result is a value: component slots, the `antlers` modifier, interpolations, user content (`Antlers::parse` on untrusted strings is never instrumented). `InstrumentationState::suppressed()` is the query.

An outermost Antlers render that happens while a Blade (or any PHP) view is buffering its output, such as `@include('card')` or `{!! Antlers::parse(...) !!}`, reads the buffered prefix through `InstrumentationState::bufferedOutputIsCommentSafe()` and suppresses itself when a comment could not follow that prefix: inside a tag or attribute value, a comment, a doctype or other bogus comment, CDATA, raw text (`script`, `style`, `textarea`, `title`, `plaintext`) or a `pre` / `listing`. `OutputContext` is the resumable tokenizer behind it; it scans each buffer level incrementally and re-validates the scanned prefix by hash, so repeated includes in one layout cost one pass over the page. `Runtime::misplacedMarkerRemovals()` and `removedMisplacedMarkerBytes()` count what the final pass still had to remove; on a page composed of Antlers and Blade views they should stay at zero.

## 9. Recipes

### Neutrality check for a tag or template

```php
$plain = (string) Antlers::parse($template, $data, true);

Runtime::register($instrumenter = HtmlInstrumentation::make());
$instrumented = (string) Antlers::parse($template, $data, true);
Runtime::forget($instrumenter);

assert($plain === HtmlInstrumentation::removeMarkers($instrumented, [$instrumenter]));
```

`tests/View/Instrumentation/InstrumentationNeutralityTest.php` is a data-provider version of this for the cases that matter (slots, modifiers, comparisons, parameters, line endings).

### Asserting markers in a test

```php
$markers = collect($this->decodedMarkers($output))->where('expression', 'title');
$this->assertSame(mb_strpos($template, '{{ title }}'), $markers->first()['start']);
```

`Tests\View\Instrumentation\Concerns\DecodesMarkers` provides `decodedMarkers()` and `markerExpressions()`. For attribute-only mode decode the `data-*` payload with `HtmlInstrumentation::decode()`.

### Building a tag-aware dev tool

1. Enable instrumentation with attributes for element-level mapping and comments for text-level mapping.
2. Walk the DOM; each `data-antlers` attribute lists the regions owned by that element, each comment pair brackets one region's output.
3. Use `view` + `line`/`start`/`end` to jump to source. Offsets are character offsets into the file, including front matter.
4. Expect no markers inside `<script>`, `<style>`, `<textarea>`, attribute values, or inside the output of tags that transform their render (markdown, `backspace`, glide `batch`). Form bodies do carry markers: `Forms\Tags` opts in.

### Keeping the static cache honest

While any HTML instrumenter is registered, the static cache middleware neither serves nor writes pages (`Runtime::instrumenting()`), and the cache tag bypasses its store. Markers therefore never end up frozen in a cached page.

## 10. Known limitations

- **Transformed renders print marker-free.** Markdown, breadcrumbs with `backspace`, glide `batch`, and any addon that post-processes its render lose inner markers for that region. The outer tag region is still marked, so nothing is misattributed. This is by design; opt in with the interface if the transformation preserves the render verbatim.
- **`(string) Antlers::parse(...)` inside a tag prints marker-free.** Return the object to keep markers.
- **Root-level regions get no comment markers at runtime.** A bare `{{ title }}` with no element ancestor is only reachable through attribute layers on an ancestor, and a string template with no elements gets nothing.
- **Custom markers (`markersUsing`) cannot be stripped.** The neutrality guarantee and the final-pass cleaner cover the default comment format and attribute layers only.
- **Parser-local instrumenters are invisible to the manager.** An instrumenter added with `$parser->preparse($instrumenter)` rather than `Runtime::register` produces markers that value paths do not strip.
- **Fixed (2026-10-05): `as=` and `no_results` bodies keep their offsets.** `NodeProcessor` now re-enters the tag body scope around the `parse`/`parseLoop`/`parseNoResults` calls it makes after the tag returns. Pinned by `HtmlInstrumentationSafetyTest::test_tag_bodies_rendered_after_the_tag_call_keep_their_authored_offsets`.
- **Fixed (2026-10-05): `TagBody::align` only keeps the marker pairs a slice contains.** A start marker at the slice end, an end marker at the slice start, and any marker whose partner falls outside the slice are dropped, so sliced bodies print balanced pairs.
- **Open: `TagBody::align` treats any unique substring of the body as a slice of it.** A tag that parses an unrelated string which happens to occur once in its body gets that region's markers and offsets.
- **Open: view renders and `Antlers::parse` at the outermost level return markers.** Code that measures them (`Str::limit(view('x')->render(), 80)` inside a tag), or Antlers mail views, which never reach the response listener, see the markers.
- **Open: `Runtime::flush()` does not drop the config-driven instrumenter.**
- **Per-request metadata grows the static node cache.** Each distinct instrumented text is cached separately; metadata that varies per request defeats that cache.
- **The final-pass cleaner is textual.** Attribute-layer look-alike text inside user content, and unusual raw-text terminators, are not perfectly distinguished from real markers. It strips markers from every place a browser would not see a comment: tag markup, attribute values, raw text (`script`, `style`, `textarea`, `title`, `plaintext` and friends), comments (ended by `-->` or `--!>`), doctypes and other `<!…>` / `<?…>` / `</ …>` bogus comments, and CDATA. Because removing text can fuse neighbouring characters into a new token, it reruns until the output is stable.
- **The HTML engine caps element chains at 512 ancestors** (Chrome's DOM depth limit); deeper nesting reports the outermost 511 names plus the owning element. Adoption-agency runs against thousands of unclosed formatting elements, and anchoring inside templates nested thousands deep, still cost time proportional to the depth per node.
- **Static caching is bypassed while instrumenting**, including nocache regions, so cached-page behaviour cannot be observed with instrumentation on.
- **A tag that throws leaves its node on `GlobalRuntimeState::$globalTagEnterStack`.** Pre-existing in 6.x; it only affects error line seeding, and the instrumentation scope no longer reads that stack.
- **Engine round-trip.** Fixed on 2026-09-30: text inside formatting reconstructed out of a `<colgroup>`/`<caption>` was anchored after whatever source anchor happened to be last in the table instead of after its own container's anchor chain (`AnchorsSourceNodes::anchorAfterSourcePositionTail`). Pinned by `DocumentTest::fosteredFormattingProvider`; 200k table-focused fuzz inputs round-trip byte for byte.

## 11. Where things live

| Concern | Location |
|---|---|
| Configuration and registration | `InstrumentationManager` via the `Runtime` facade |
| Analysis and marker emission | `HtmlInstrumentation`, `Antlers/TemplateAnalyzer`, `Concerns/EmitsInstrumentation` |
| Marker helpers | `Concerns/RemovesMisplacedMarkers` (`removeMarkers`, `extractMarkers`, `removeMisplacedMarkers`, `trimAroundMarkers`), `HtmlInstrumentation::regions()` |
| Final-pass cleaner | `MisplacedMarkerRemover` (skip-ahead regex built from named pieces, then a tag/attribute/raw-text scanner) |
| Per-render state | `InstrumentationState` (suppression, tag body scope, component and view depth, source seeds) |
| Tag body split and alignment | `TagBody`, memoised on `AntlersNode::$tagBody` |
| Runtime integration | `RuntimeParser::renderText` (alignment, `$instrument`, twin return), `NodeProcessor` (`tagBodyFor`, `tagBodyOutput`, `instrumentedOutput`, slot twins) |
| Render objects | `AntlersString` (`instrumented()`, lazy marker-free cast), `AntlersLoop` |
| Opt-in | `Statamic\Contracts\View\Instrumentation\ComposesRenderedOutput` |
| Response cleanup | `RemoveMisplacedMarkersFromResponse` on `RequestHandled` |
| HTML document engine | `View/Antlers/Language/Analyzers/Html/*` |

Tests: `tests/View/Instrumentation/*` (behaviour, neutrality, lifecycle, forms, slots, nocache, response), `tests/Antlers/Runtime/HtmlInstrumentationSafetyTest.php` (unsafe contexts and tag bodies), `tests/Antlers/Runtime/SpanTracingTest.php` (tracers), `tests/Antlers/Parser/Html/*` (engine).

## 12. Verification recipe for this box

Never run two PHPUnit processes at once; they share the testbench storage and fail with `mkdir(): File exists`. Never run the full suite or `tests/Console/Commands`.

```
php vendor/phpunit/phpunit/phpunit tests/View/Instrumentation tests/Antlers/Runtime/HtmlInstrumentationSafetyTest.php tests/Antlers/Runtime/SpanTracingTest.php tests/Antlers/Runtime/LegacyTracerBalanceTest.php tests/Antlers/Runtime/TraceManagerErrorsTest.php tests/StaticCaching/InstrumentationBypassTest.php
php vendor/phpunit/phpunit/phpunit tests/Antlers
php vendor/phpunit/phpunit/phpunit tests/View
php vendor/phpunit/phpunit/phpunit tests/Tags      # GlideTest::it_outputs_a_data_url fails on this box regardless
php vendor/phpunit/phpunit/phpunit tests/Forms
php vendor/phpunit/phpunit/phpunit tests/StaticCaching
```
