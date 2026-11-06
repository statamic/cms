<?php

namespace Tests\View\Instrumentation;

use RuntimeException;
use Statamic\Facades\Antlers;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Tests\TestCase;

class FluentInstrumentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::$isEvaluatingUserData = false;
    }

    public function tearDown(): void
    {
        Runtime::flush();

        parent::tearDown();
    }

    public function test_only_limits_markers_to_matching_expressions()
    {
        $instrumenter = HtmlInstrumentation::make()->only('title', 'partial:*');

        $expressions = $instrumenter
            ->regions($instrumenter->instrument('<p>{{ title }}</p><p>{{ subtitle }}</p><div>{{ partial:card }}</div>'))
            ->pluck('expression')
            ->all();

        $this->assertSame(['title', 'partial:card'], $expressions);
    }

    public function test_except_removes_matching_expressions()
    {
        $instrumenter = HtmlInstrumentation::make()->except(['sub*']);

        $expressions = $instrumenter
            ->regions($instrumenter->instrument('<p>{{ title }}</p><p>{{ subtitle }}</p>'))
            ->pluck('expression')
            ->all();

        $this->assertSame(['title'], $expressions);
    }

    public function test_constraints_stack()
    {
        $instrumenter = HtmlInstrumentation::make()
            ->only('title', 'subtitle', 'body')
            ->except('subtitle')
            ->filter(fn (array $metadata) => $metadata['line'] > 1);

        $expressions = $instrumenter
            ->regions($instrumenter->instrument("<p>{{ title }}</p>\n<p>{{ subtitle }}</p>\n<p>{{ body }}</p>"))
            ->pluck('expression')
            ->all();

        $this->assertSame(['body'], $expressions);
    }

    public function test_attributes_accepts_several_names()
    {
        $instrumented = HtmlInstrumentation::make()
            ->withoutComments()
            ->attributes('data-a', 'data-b')
            ->instrument('<p>{{ title }}</p>');

        $this->assertStringContainsString(' data-a="', $instrumented);
        $this->assertStringContainsString(' data-b="', $instrumented);
    }

    public function test_regions_reads_comment_and_attribute_layers_in_document_order()
    {
        $instrumenter = HtmlInstrumentation::make()->withoutComments()->attributes()->jsonAttribute('data-json');

        $regions = $instrumenter->regions($instrumenter->instrument('<p>{{ title }}</p><div>{{ body }}</div>'));

        $this->assertSame(['title', 'title', 'body', 'body'], $regions->pluck('expression')->all());

        $commented = HtmlInstrumentation::make();
        $html = $commented->instrument('<p>{{ title }}</p><div>{{ body }}</div>');

        $this->assertSame(['title', 'body'], $commented->regions($html)->pluck('expression')->all());
        $this->assertSame(1, $commented->regions($html)->firstWhere('expression', 'title')['line']);
    }

    public function test_regions_ignores_attribute_values_it_cannot_decode()
    {
        $instrumenter = HtmlInstrumentation::make()->withoutComments()->attribute('data-first', fn (array $regions) => $regions[0]['expression']);

        $html = $instrumenter->instrument('<p>{{ title }}</p>');

        $this->assertStringContainsString('<p data-first="title">', $html);
        $this->assertTrue($instrumenter->regions($html)->isEmpty());
    }

    public function test_using_registers_for_the_duration_of_the_callback()
    {
        $instrumenter = HtmlInstrumentation::make();

        $html = Runtime::using($instrumenter, fn () => (string) Antlers::parse('<p>{{ title }}</p>', ['title' => 'Hello'], true));

        $this->assertSame(['title'], Runtime::using($instrumenter, fn () => Runtime::regions($html))->pluck('expression')->all());
        $this->assertFalse(Runtime::instrumenting());
        $this->assertSame('<p>Hello</p>', (string) Antlers::parse('<p>{{ title }}</p>', ['title' => 'Hello'], true));
    }

    public function test_using_forgets_the_instrumenter_when_the_callback_throws()
    {
        try {
            Runtime::using(HtmlInstrumentation::make(), fn () => throw new RuntimeException('boom'));
        } catch (RuntimeException) {
        }

        $this->assertFalse(Runtime::instrumenting());
    }

    public function test_using_keeps_an_instrumenter_that_was_already_registered()
    {
        Runtime::register($instrumenter = HtmlInstrumentation::make());

        Runtime::using($instrumenter, fn () => null);

        $this->assertTrue(Runtime::instrumenting());
    }
}
