<?php

namespace Tests\View\Instrumentation;

use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Instrumentation\OutputContext;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class BladeOutputContextTest extends TestCase
{
    use FakesViews, PreventSavingStacheItemsToDisk;

    #[DataProvider('prefixProvider')]
    public function test_the_output_context_knows_whether_a_comment_can_follow_the_buffered_html(string $prefix, bool $safe)
    {
        $context = new OutputContext;
        $context->advance($prefix);

        $this->assertSame($safe, $context->isCommentSafe(), $prefix);

        $incremental = new OutputContext;

        foreach (str_split($prefix, 3) as $chunk) {
            $buffered = ($buffered ?? '').$chunk;
            $incremental->advance($buffered);
        }

        $this->assertSame($safe, $incremental->isCommentSafe(), 'incremental: '.$prefix);
    }

    public static function prefixProvider(): array
    {
        return [
            'empty' => ['', true],
            'content' => ['<div class="a">Hello ', true],
            'after closed tags' => ['<p><b>x</b></p>', true],
            'inside tag' => ['<div class="a" ', false],
            'tag name' => ['<div', false],
            'lone less-than' => ['a < b', true],
            'trailing less-than' => ['a <', false],
            'partial comment opener' => ['x<!-', false],
            'partial cdata opener' => ['<svg><![CDA', false],
            'double quoted value' => ['<input value="', false],
            'single quoted value' => ["<input value='x", false],
            'quote with angle bracket inside' => ['<input value="a>b" ', false],
            'closed quoted value' => ['<input value="a>b">', true],
            'unquoted value' => ['<input value=x', false],
            'unquoted value ended' => ['<input value=x >', true],
            'equals then end' => ['<input value=>', true],
            'comment' => ['<!-- note ', false],
            'closed comment' => ['<!-- note -->', true],
            'bang closed comment' => ['<!-- note --!>', true],
            'abrupt comment' => ['<!-->', true],
            'doctype' => ['<!DOCTYPE html ', false],
            'closed doctype' => ['<!DOCTYPE html>', true],
            'xml declaration' => ['<?xml version="1.0" ', false],
            'closed xml declaration' => ['<?xml version="1.0"?>', true],
            'cdata' => ['<svg><![CDATA[ > ', false],
            'closed cdata' => ['<svg><![CDATA[ > ]]>', true],
            'bogus closing tag' => ['</ x ', false],
            'empty closing tag' => ['</>', true],
            'script' => ['<script>var a = "', false],
            'script with fake end' => ['<script>var a = "</scriptx>";', false],
            'closed script' => ['<SCRIPT>var a = 1;</script >', true],
            'textarea' => ['<textarea rows=3>', false],
            'closed textarea' => ['<textarea>x</TEXTAREA>', true],
            'title' => ['<title>', false],
            'style self closing' => ['<style/>', false],
            'pre' => ['<pre>', false],
            'closed pre' => ['<pre>x</pre>', true],
            'nested pre' => ['<pre><pre>x</pre>', false],
            'plaintext' => ['<plaintext></plaintext>', false],
        ];
    }

    public function test_antlers_views_placed_by_blade_suppress_their_markers_where_a_comment_cannot_go()
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => true]]);
        $this->withFakeViews();
        $this->viewShouldReturnRaw('card', '<em>{{ title }}</em>');
        $this->viewShouldReturnRaw(
            'blade-page',
            '<title>@include("card")</title><textarea>@include("card")</textarea><input value="@include(\'card\')"><script>@include("card")</script><!-- @include("card") --><div>@include("card")</div><p>{!! \Statamic\Facades\Antlers::parse("<u>{"."{ title }}</u>", ["title" => $title], true) !!}</p><input value="{!! \Statamic\Facades\Antlers::parse("<u>{"."{ title }}</u>", ["title" => $title], true) !!}">',
            'blade.php'
        );

        $output = view('blade-page', ['title' => 'Hello'])->render();

        $this->assertStringStartsWith(
            '<title><em>Hello</em></title><textarea><em>Hello</em></textarea><input value="<em>Hello</em>"><script><em>Hello</em></script><!-- <em>Hello</em> --><div><em><!-- antlers:start ',
            $output
        );
        $this->assertStringContainsString('</div><p><u><!-- antlers:start ', $output);
        $this->assertStringEndsWith('</u></p><input value="<u>Hello</u>">', $output);
        $this->assertSame(2, substr_count($output, '<!-- antlers:start '));
        $this->assertSame($output, Runtime::removeMisplacedMarkers($output));
        $this->assertSame(0, Runtime::misplacedMarkerRemovals());
    }

    public function test_the_final_pass_counts_what_it_still_has_to_remove()
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => true]]);
        $marker = '<!-- antlers:start '.base64_encode('{"id":1}').' -->';

        $this->assertSame('<input value="Hello">', Runtime::removeMisplacedMarkers('<input value="'.$marker.'Hello<!-- antlers:end 1 -->">'));
        $this->assertSame(1, Runtime::misplacedMarkerRemovals());
        $this->assertSame(strlen($marker) + strlen('<!-- antlers:end 1 -->'), Runtime::removedMisplacedMarkerBytes());
    }
}
