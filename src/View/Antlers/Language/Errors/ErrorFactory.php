<?php

namespace Statamic\View\Antlers\Language\Errors;

use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class ErrorFactory
{
    /**
     * Creates a new syntax error with the provided information.
     *
     * @param  string  $type  The type of syntax error.
     * @param  AbstractNode  $token  The abstract node representing this token.
     * @param  string  $message  The error message to display to the user.
     * @return SyntaxErrorException
     */
    public static function makeSyntaxError($type, $token, $message)
    {
        $syntaxException = new SyntaxErrorException($message);
        $syntaxException->node = $token;
        $syntaxException->type = $type;

        return $syntaxException;
    }

    /**
     * Creates a new runtime error with the provided information.
     *
     * @param  string  $type  The type of runtime error.
     * @param  AbstractNode  $token  The abstract node representing this token.
     * @param  string  $message  The error message to display to the user.
     * @return RuntimeException
     */
    public static function makeRuntimeError($type, $token, $message)
    {
        $runtimeException = new RuntimeException($message);
        $runtimeException->node = $token;
        $runtimeException->type = $type;

        return $runtimeException;
    }

    public static function pluralParameters($count)
    {
        if ($count == 1) {
            return 'parameter';
        }

        return 'parameters';
    }
}
