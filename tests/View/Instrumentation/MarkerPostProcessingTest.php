<?php

namespace Tests\View\Instrumentation;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\Antlers\ComponentSourceMap;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Instrumentation\InstrumentationManager;
use Statamic\View\Instrumentation\InstrumentationState;
use Tests\TestCase;

class MarkerPostProcessingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::$isEvaluatingUserData = false;
    }

    public function test_the_final_pass_scans_output_it_already_cleaned_only_once()
    {
        $manager = new CountingInstrumentationManager;
        $manager->register(HtmlInstrumentation::make()->attributes('data-antlers'));
        [$start, $end, $layer] = $this->markers();

        $dirty = '<main'.$layer.'>'.$start.'Hello'.$end.'<input value="'.$start.'x'.$end.'"><script>'.$start.'y'.$end.'</script></main>';
        $clean = '<main'.$layer.'>'.$start.'Hello'.$end.'<input value="x"><script>y</script></main>';

        $this->assertSame($clean, $manager->removeMisplacedMarkers($dirty));
        $this->assertSame(1, $manager->scans);

        $this->assertSame($clean, $manager->removeMisplacedMarkers($clean));
        $this->assertSame($clean, $manager->removeMisplacedMarkers($clean));
        $this->assertSame(1, $manager->scans);

        $this->assertSame($clean, $manager->removeMisplacedMarkers($dirty));
        $this->assertSame(2, $manager->scans);

        $this->assertSame($clean.' ', $manager->removeMisplacedMarkers($clean.' '));
        $this->assertSame(3, $manager->scans);

        $this->assertSame($clean.' ', $manager->removeMisplacedMarkers($clean.' '));
        $this->assertSame(3, $manager->scans);
    }

    public function test_the_final_pass_memo_is_scoped_to_the_registered_instrumenters()
    {
        $manager = new CountingInstrumentationManager;
        $manager->register($instrumenter = HtmlInstrumentation::make());
        [$start, $end] = $this->markers();

        $clean = '<p>'.$start.'Hello'.$end.'</p>';

        $this->assertSame($clean, $manager->removeMisplacedMarkers($clean));
        $this->assertSame($clean, $manager->removeMisplacedMarkers($clean));
        $this->assertSame(1, $manager->scans);

        $manager->register(HtmlInstrumentation::make()->comments('trace'));

        $this->assertSame($clean, $manager->removeMisplacedMarkers($clean));
        $this->assertSame(2, $manager->scans);

        $manager->flush();
        $manager->register($instrumenter);

        $this->assertSame($clean, $manager->removeMisplacedMarkers($clean));
        $this->assertSame(3, $manager->scans);

        $manager->flush();

        $this->assertSame($clean, $manager->removeMisplacedMarkers($clean));
        $this->assertSame(3, $manager->scans);
    }

    public function test_the_final_pass_memo_follows_instrumenter_reconfiguration()
    {
        $manager = new CountingInstrumentationManager;
        $manager->register($instrumenter = HtmlInstrumentation::make());
        [$start, $end, $layer] = $this->markers();

        $html = '<p>'.$start.'Hello'.$end.'</p><input value="x'.$layer.'">';

        $this->assertSame($html, $manager->removeMisplacedMarkers($html));
        $this->assertSame($html, $manager->removeMisplacedMarkers($html));
        $this->assertSame(1, $manager->scans);

        $instrumenter->attributes('data-antlers');

        $this->assertSame('<p>'.$start.'Hello'.$end.'</p><input value="x">', $manager->removeMisplacedMarkers($html));
        $this->assertSame(2, $manager->scans);
    }

    public function test_the_final_pass_strips_markers_of_instrumenters_registered_only_on_the_parser()
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => true]]);

        $parser = app(ParserContract::class);
        $parser->preparse(HtmlInstrumentation::make()->comments('local'));

        $attrs = '<!-- local:start eyJpZCI6MX0= --> class="a"<!-- local:end 1 --> data-x="<!-- antlers:start eyJpZCI6Mn0= -->1<!-- antlers:end 2 -->"';
        $rendered = (string) $parser->parse('<div {{ attrs }}>x</div><p>{{ title }}</p>', ['attrs' => $attrs, 'title' => 'Hi']);

        $this->assertStringStartsWith('<div  class="a" data-x="1">x</div><p><!-- antlers:start ', $rendered);
        $this->assertStringContainsString('<!-- local:start ', $rendered);
        $this->assertSame(
            '<div  class="a" data-x="1">x</div><p>Hi</p>',
            preg_replace('/<!-- (?:antlers|local):(?:start [A-Za-z0-9+\/=]+|end \d+) -->/', '', $rendered)
        );
    }

    public function test_the_final_pass_ends_comments_where_browsers_do()
    {
        $manager = new CountingInstrumentationManager;
        $manager->register(HtmlInstrumentation::make());
        [$start, $end] = $this->markers();

        $bang = '<!-- note --!><p>'.$start.'x'.$end.'</p><!-- z -->';

        $this->assertSame($bang, $manager->removeMisplacedMarkers($bang));
        $this->assertSame('<!-- note x --!><p>'.$start.'x'.$end.'</p>', $manager->removeMisplacedMarkers('<!-- note '.$start.'x'.$end.' --!><p>'.$start.'x'.$end.'</p>'));
        $this->assertSame('<!-- note x -->', $manager->removeMisplacedMarkers('<!-- note '.$start.'x'.$end.' -->'));
    }

    #[DataProvider('splitMarkersProvider')]
    public function test_separating_markers_returns_the_plain_value_and_its_instrumented_twin(string $output, string $value, ?string $twin)
    {
        $manager = new CountingInstrumentationManager;
        $manager->register(HtmlInstrumentation::make()->attributes('data-antlers'));
        $decode = fn (?string $text) => $text === null ? null : str_replace(['[S]', '[E]', '[L]'], $this->markers(), $text);

        $this->assertSame([$value, $decode($twin)], $manager->splitMarkers($decode($output)));

        if ($twin !== null) {
            $this->assertSame($value, $manager->removeMarkers($decode($twin)));
        }
    }

    public static function splitMarkersProvider(): array
    {
        return [
            'plain output is trimmed' => [" \n<b>Hello</b>\n ", '<b>Hello</b>', null],
            'authored comments are not markers' => ['<!-- antlers --> ', '<!-- antlers -->', null],
            'whitespace only' => ["\n\t", '', null],
            'user text resembling a layer' => [' data-antlers="x" text', 'data-antlers="x" text', null],
            'user text resembling a layer after a newline' => ["\n data-antlers=\"x\"", 'data-antlers="x"', null],
            'markers around the value' => [" \n[S]<b[L]>Hello</b>[E]\n ", '<b>Hello</b>', '[S]<b[L]>Hello</b>[E]'],
            'markers inside the value' => ["\n <i>[S]Hi[E]</i> ", '<i>Hi</i>', '<i>[S]Hi[E]</i>'],
            'markers around whitespace only' => [" [S]\n[E] ", '', '[S][E]'],
        ];
    }

    public function test_result_cache_keys_change_with_every_input_that_changes_instrumentation()
    {
        $instrumenter = HtmlInstrumentation::make();
        $method = new ReflectionMethod($instrumenter, 'resultCacheKey');
        $key = fn (string $template = '<p>{{ a }}</p>', ?string $view = 'views/page.antlers.html', bool $root = true) => $method->invoke($instrumenter, $template, $view, $root);
        $base = $key();

        $this->assertSame($base, $key());
        $this->assertSame($base, $method->invoke(HtmlInstrumentation::make()->attributes('data-antlers'), '<p>{{ a }}</p>', 'views/page.antlers.html', true));
        $this->assertNotSame($base, $key('<p>{{ b }}</p>'));
        $this->assertNotSame($base, $key(view: 'views/other.antlers.html'));
        $this->assertNotSame($base, $key(view: null));
        $this->assertNotSame($base, $key(root: false));
        $this->assertNotSame($base, InstrumentationState::withSourceSeed([2, 0, true], $key));
        $this->assertNotSame($base, InstrumentationState::withSourceSeed([1, 5, true], $key));
        $this->assertNotSame($base, InstrumentationState::withSourceSeed([1, 0, false], $key));
        $this->assertNotSame($base, InstrumentationState::withSourceMap(new ComponentSourceMap('<x-card />'), $key));
        $this->assertNotSame(
            InstrumentationState::withSourceMap(new ComponentSourceMap('<x-card />'), $key),
            InstrumentationState::withSourceMap(new ComponentSourceMap('<x-card>a</x-card>'), $key)
        );
        $this->assertNotSame($key(view: 'a:1'), $key(view: 'a', template: '1'));
        $this->assertNotSame($key(view: "a\0b"), $key(view: 'a', template: "\0b"));
    }

    private function markers(): array
    {
        return [
            '<!-- antlers:start '.base64_encode('{"id":1}').' -->',
            '<!-- antlers:end 1 -->',
            ' data-antlers="W10="',
        ];
    }
}

class CountingInstrumentationManager extends InstrumentationManager
{
    public int $scans = 0;

    protected function cleanMisplacedMarkers(string $html, array $instrumenters): string
    {
        $this->scans++;

        return parent::cleanMisplacedMarkers($html, $instrumenters);
    }
}
