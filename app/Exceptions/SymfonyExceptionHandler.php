<?php

namespace Statamic\Exceptions;

use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;

class SymfonyExceptionHandler extends ExceptionHandler
{
    /**
     * Gets the stylesheet associated with the given exception.
     *
     * @return string The stylesheet as a string
     */
    public function getStylesheet(FlattenException $exception)
    {
        return tap(parent::getStylesheet($exception), function (&$css) {
            $rad = config('app.rad_exceptions', true);

            $css .= 'body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; }';
            $css .= '.exception-illustration { display: none; }';

            if ($rad) {
                $css = str_replace('B0413E', 'ff469c', $css);
                $css .= '.exception-summary {';
                $css .= 'background-image: -webkit-gradient(linear,right top,left top,from(#a832d7),color-stop(90%,#ff269e));';
                $css .= 'background-image: linear-gradient(-90deg,#a832d7,#ff269e 90%); }';
            } else {
                $css = str_replace('B0413E', '3C4858', $css);
            }
        });
    }
}