<?php

namespace Tests\View\Instrumentation;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\View\Instrumentation\RemoveMisplacedMarkersFromResponse;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ResponseInstrumentationTest extends TestCase
{
    use FakesViews, PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::get('/instrumented-page', fn () => view('blade-page', ['title' => 'Hello']));
            Route::get('/instrumented-json', fn () => response()->json(['html' => view('card', ['title' => 'Hello'])->render()]));
        });
    }

    private function fakeViews(bool $instrumented)
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => $instrumented]]);

        $this->withFakeViews();
        $this->viewShouldReturnRaw('card', '<em>{{ title }}</em>');
        $this->viewShouldReturnRaw(
            'blade-page',
            '<title>@include("card")</title><textarea>@include("card")</textarea><input value="@include(\'card\')"><!-- @include("card") --><div>@include("card")</div>',
            'blade.php'
        );
    }

    public function test_responses_that_are_not_html_are_left_alone()
    {
        $this->fakeViews(true);

        $this->assertStringContainsString('<em><!-- antlers:start ', $this->get('/instrumented-json')->assertOk()->json('html'));
    }

    public function test_responses_are_untouched_without_instrumentation()
    {
        $this->fakeViews(false);

        $this->assertSame(
            '<title><em>Hello</em></title><textarea><em>Hello</em></textarea><input value="<em>Hello</em>"><!-- <em>Hello</em> --><div><em>Hello</em></div>',
            $this->get('/instrumented-page')->assertOk()->getContent()
        );
    }

    public function test_cleaning_misplaced_markers_updates_the_content_length()
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => true]]);
        $marker = '<!-- antlers:start '.base64_encode('{"id":1}').' -->';
        $body = '<input value="'.$marker.'Hello<!-- antlers:end 1 -->"><p>'.$marker.'Hello<!-- antlers:end 1 --></p>';
        $response = new Response($body, 200, ['Content-Type' => 'text/html', 'Content-Length' => strlen($body)]);

        app(RemoveMisplacedMarkersFromResponse::class)->handle(new RequestHandled(Request::create('/'), $response));

        $this->assertSame('<input value="Hello"><p>'.$marker.'Hello<!-- antlers:end 1 --></p>', $response->getContent());
        $this->assertSame((string) strlen($response->getContent()), $response->headers->get('Content-Length'));
    }

    public function test_cleaning_misplaced_markers_keeps_the_original_view_and_a_matching_etag()
    {
        $this->fakeViews(true);

        $this->get('/instrumented-page')->assertOk()->assertViewIs('blade-page')->assertViewHas('title', 'Hello');

        $marker = '<!-- antlers:start '.base64_encode('{"id":1}').' -->';
        $body = '<input value="'.$marker.'Hello<!-- antlers:end 1 -->">';
        $response = (new Response($body, 200, ['Content-Type' => 'text/html']))->setEtag(md5($body));
        $unrelated = (new Response($body, 200, ['Content-Type' => 'text/html']))->setEtag('v1');

        app(RemoveMisplacedMarkersFromResponse::class)->handle(new RequestHandled(Request::create('/'), $response));
        app(RemoveMisplacedMarkersFromResponse::class)->handle(new RequestHandled(Request::create('/'), $unrelated));

        $this->assertSame('<input value="Hello">', $response->getContent());
        $this->assertSame('"'.md5('<input value="Hello">').'"', $response->getEtag());
        $this->assertSame('"v1"', $unrelated->getEtag());
    }

    public function test_invalid_configuration_is_ignored_by_the_listener_but_still_fails_rendering()
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => true, 'prefix' => 'bad -->']]);
        $body = '<input value="<!-- antlers:start eyJpZCI6MX0= -->Hello<!-- antlers:end 1 -->">';
        $response = new Response($body, 200, ['Content-Type' => 'text/html']);

        app(RemoveMisplacedMarkersFromResponse::class)->handle(new RequestHandled(Request::create('/'), $response));

        $this->assertSame($body, $response->getContent());

        $this->expectException(InvalidArgumentException::class);
        app(ParserContract::class);
    }
}
