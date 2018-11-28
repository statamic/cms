<?php

namespace Statamic\Exceptions;

use Exception;
use Statamic\Statamic;
use Illuminate\Support\Facades\View;
use App\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;

class Handler extends ExceptionHandler
{
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
