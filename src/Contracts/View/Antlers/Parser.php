<?php

namespace Statamic\Contracts\View\Antlers;

use Statamic\View\Antlers\AntlersString;

interface Parser
{
    /**
     * Parses the text.
     *
     * @param  string  $text  Text to parse
     * @param  array|object  $data  Array or object to use
     * @return AntlersString
     */
    public function parse($text, $data = []);

    /**
     * Ignore tags-who-must-not-be-parsed.
     *
     * @param  string  $text  The text to extract from
     * @return string
     */
    public function extractNoparse($text);

    /**
     * Sets whether or not PHP code should be evaluated.
     *
     * @param  bool  $allow  Whether PHP is allowed.
     * @return Parser
     */
    public function allowPhp($allow = true);

    /**
     * Parses a view file.
     *
     * @param  string  $view  The view path.
     * @param  string  $text  The view contents.
     * @param  array  $data  The data.
     * @return AntlersString
     */
    public function parseView($view, $text, $data = []);

    /**
     * Injects noparse extractions.
     *
     * This is so that multiple parses can store noparse
     * extractions and all noparse can then be injected right
     * before data is displayed.
     *
     * @param  string  $text  Text to inject into
     * @return string
     */
    public function injectNoparse($text);

    public function valueWithNoparse($text);

    /**
     * Takes a scope-notated key and finds the value for it in the given
     * array or object.
     *
     * @param  string  $key  Dot-notated key to find
     * @param  array|object  $data  Array or object to search
     * @param  mixed  $default  Default value to use if not found
     * @return mixed
     */
    public function getVariable($key, $context, $default = null);

    /**
     * Sets a render callback.
     *
     * @param $callback
     * @return Parser
     */
    public function callback($callback);

    public function cascade($cascade);
}
