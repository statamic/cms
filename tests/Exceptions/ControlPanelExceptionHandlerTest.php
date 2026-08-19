<?php

namespace Tests\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\ControlPanelExceptionHandler;
use Tests\TestCase;

class ControlPanelExceptionHandlerTest extends TestCase
{
    #[Test]
    public function it_renders_validation_errors_as_json_even_when_the_app_restricts_json_rendering()
    {
        $handler = app(ControlPanelExceptionHandler::class);

        $handler->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        $request = Request::create('/cp/collections/pages/entries/123', 'PATCH');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $exception = ValidationException::withMessages(['title' => 'The title field is required.']);

        $response = $handler->render($request, $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('title', $response->getData(true)['errors']);
    }
}
