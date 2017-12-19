<?php

namespace Statamic\Exceptions;

use Exception;
use Symfony\Component\Debug\Exception\FlattenException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }

    /**
     * Render an exception to a string using Symfony.
     *
     * @param  \Exception  $e
     * @param  bool  $debug
     * @return string
     */
    protected function renderExceptionWithSymfony(Exception $e, $debug)
    {
        return (new SymfonyExceptionHandler($debug))->getHtml(
            FlattenException::create($e)
        );
    }
}
