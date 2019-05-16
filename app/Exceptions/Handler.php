<?php

namespace Statamic\Exceptions;

use Exception;
use Statamic\Statamic;
use Illuminate\Support\Facades\View;
use App\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Illuminate\Auth\Access\AuthorizationException as IlluminateAuthException;

class Handler extends ExceptionHandler
{
    public function render($request, Exception $e)
    {
        if ($e instanceof IlluminateAuthException && !$request->expectsJson()) {
            return back_or_route('statamic.cp.index')->withError($e->getMessage());
        }

        return parent::render($request, $e);
    }

    protected function whoopsHandler()
    {
        return (new WhoopsHandler)->forDebug();
    }

    /**
     * Temporarily disable Whoops even if it's installed.
     *
     * See https://github.com/filp/whoops/issues/562
     */
    protected function renderExceptionWithWhoops(Exception $e)
    {
        return $this->renderExceptionWithSymfony($e, config('app.debug'));
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

    /**
     * The Control Panel should use its own error views.
     */
    protected function registerErrorViewPaths()
    {
        if (! Statamic::isCpRoute()) {
            return parent::registerErrorViewPaths();
        }

        $path = View::getFinder()->getHints()['statamic'][0] . '/errors';

        View::replaceNamespace('errors', $path);
    }
}
