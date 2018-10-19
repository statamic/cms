<?php

namespace Statamic\View\Antlers;

use Statamic\API\Arr;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\View\Modify;
use Statamic\Exceptions\ParsingException;
use Statamic\Exceptions\ModifierException;
use Statamic\Contracts\Data\Taxonomies\Term;

class Parser
{
    // Instance state
    protected $allowPhp = false;
    protected $cumulativeNoparse = false;
    protected $inCondition = false;
    protected $data = null;
    protected $original_text = null;
    protected $callbackData = [];
    protected $conditionalData = [];

    // Regexes
    protected $callbackBlockRegex;
    protected $callbackLoopTagRegex;
    protected $callbackNameRegex;
    protected $callbackTagRegex;
    protected $conditionalElseRegex;
    protected $conditionalEndRegex;
    protected $conditionalExistsRegex;
    protected $conditionalNotRegex;
    protected $conditionalRegex;
    protected $looseVariableRegex;
    protected $noparseRegex;
    protected $recursiveRegex;
    protected $tagRegex;
    protected $variableLoopRegex;
    protected $variableRegex;
    protected $variableTagRegex;

    // Extractions
    protected static $extractions = [
        'noparse' => [],
    ];

    /**
     * Initialize the army of regexes
     */
    public function __construct()
    {
        // expand allowed characters in variable regex
        $this->variableRegex = "\b(?!if|unless\s)[a-zA-Z0-9_][|a-zA-Z\-\+\*%\#\^\@\/,0-9_\.':]*";

        // Allow spaces after the variable name so you can do modifiers like | this | and_that
        $this->looseVariableRegex = "\b(?!if|unless\s)[a-zA-Z0-9_][|a-zA-Z\-\+\*%\#\^\@\/,0-9_\.(\s.*)?':]*";

        // Different from variable regex somehow.
        $this->callbackNameRegex = '\b(?!if|unless\s)[a-zA-Z0-9_][|a-zA-Z\-\+\*%\^\/,0-9_\.(\s.*?):]*:'.$this->variableRegex;

        $this->variableLoopRegex = '/\{\{\s*('.$this->looseVariableRegex.')\s*\}\}(.*?)\{\{\s*\/\1\s*\}\}/ms';

        // expanded to allow `or` options in variable tags
        $this->variableTagRegex = '/\{\{\s*('.$this->looseVariableRegex.'(?:\s*or\s*(?:'.$this->looseVariableRegex.'|".*?"))*)\s*\}\}/m';

        // make the space-anything after the variable regex optional allowing {{tags}} and {{ tags }}
        $this->callbackBlockRegex = '/\{\{\s*('.$this->variableRegex.')(\s.*?)?\}\}(.*?)\{\{\s*\/\1\s*\}\}/ms';

        $this->recursiveRegex = '/\{\{\s*\*recursive\s*('.$this->variableRegex.')\*\s*\}\}/ms';

        $this->noparseRegex = '/\{\{\s*noparse\s*\}\}(.*?)\{\{\s*\/noparse\s*\}\}/ms';

        $this->ignoreRegex = '/@{{(?:(?!}}).)*}}/';

        $this->conditionalRegex = '/\{\{\s*(if|unless|elseif|elseunless)\s*((?:\()?(.*?)(?:\))?)\s*\}\}/ms';
        $this->conditionalElseRegex = '/\{\{\s*else\s*\}\}/ms';
        $this->conditionalEndRegex = '/\{\{\s*(?:endif|\/if|\/unless)\s*\}\}/ms';
        $this->conditionalExistsRegex = '/(\s+|^)exists\s+('.$this->variableRegex.')(\s+|$)/ms';
        $this->conditionalNotRegex = '/(\s+|^)not(\s+|$)/ms';

        // Large strings can spawn many backtrack attempts. Let's crank this sucker up.
        ini_set('pcre.backtrack_limit', Config::get('parser_backtrack_limit', 1000000));
    }

    /**
     * Kick off the Antlers parse process
     *
     * @param  string        $text      Text to parse
     * @param  array|object  $data      Array or object to use
     * @param  mixed         $callback  Callback to use for Callback Tags
     * @param  boolean       $allowPhp  Should we allow PHP?
     * @return string
     */
    public function parse($text, $data = [], $callback = false, $allowPhp = false)
    {
        $this->allowPhp = $allowPhp;

        // Let's store the current callback data with the the local data
        // so we can use it straight after a callback is called.
        $this->callbackData = $data;

        // Save the original text coming in so that we can parse it recursively
        // later on without this needing to be within a callback
        $this->original_text = $text;

        // Prevent the parsing of PHP by b0rking the PHP open tag
        if (! $allowPhp) {
            $text = str_replace(['<?php'], ['&lt;?php'], $text);
        }

        // We want to extract the noparse blocks before comments,
        // allowing us to show them for documentation purposes

        $text = $this->extractNoparse($text);
        $text = $this->stripComments($text);

        $text = $this->extractLoopedTags($text, $data, $callback);

        // Parse conditionals first to avoid parsing and execution.
        $text = $this->parseConditionals($text, $data, $callback);
        $text = $this->injectExtractions($text, 'looped_tags');
        $text = $this->parseVariables($text, $data, $callback);
        $text = $this->injectExtractions($text, 'callback_blocks');

        if ($callback) {
            $text = $this->parseCallbackTags($text, $data, $callback);
        }

        // Ensure that {{ noparse }} is never parsed, even during consecutive
        // parse calls. First, set $cumulativeNoparse to true and then use
        // self::injectNoparse($text) immediately before the final output is
        // sent to the browser
        if (! $this->cumulativeNoparse) {
            $text = $this->injectExtractions($text);
        }

        // Parse parameters inside tag pairs
        if (strpos($text, "{{") !== false) {
            $text = $this->parseCallbackTags($text, $data, null);
        }

        return $text;
    }

    /**
     * Strip all comments out of the markup
     *
     * @param  string $text
     * @return string
     */
    public function stripComments($text)
    {
        return preg_replace('/\{\{#.*?#\}\}/s', '', $text);
    }

    /**
     * Recursively parses all of the variables in the given HTML markup.
     *
     * @param  string       $html      The HTML markup
     * @param  array|object $data      the data
     * @param  callable     $callback  optional callback
     * @return string
     */
    public function parseVariables($html, $data, $callback = null)
    {
        $html = $this->parseLoopVariables($html, $data, $callback);
        $html = $this->parseStringVariables($html, $data, $callback);
        $html = $this->parseVariablesWithParameterStyleModifiers($html, $data, $callback);

        return $html;
    }

    /**
     * Look for and parse array variables
     *
     * @param  string       $html      The HTML markup
     * @param  array|object $data      the data
     * @param  callable     $callback  optional callback
     * @return string
     */
    public function parseLoopVariables($text, $data, $callback)
    {
        // Check for any vars flagged as noparse
        $noparse = array_get($data, '_noparse', []);

        /**
         * $data_matches[][0][0] is the raw data loop tag
         * $data_matches[][0][1] is the offset of raw data loop tag
         * $data_matches[][1][0] is the data variable
         * $data_matches[][1][1] is the offset of data variable
         * $data_matches[][2][0] is the content to be looped over
         * $data_matches[][2][1] is the offset of content to be looped over
         */
        if (preg_match_all($this->variableLoopRegex, $text, $data_matches, PREG_SET_ORDER + PREG_OFFSET_CAPTURE)) {
            foreach ($data_matches as $match) {

                // Don't parse any variables in the noparse list.
                $var_name = (strpos($match[1][0], '|') !== false) ? substr($match[1][0], 0, strpos($match[1][0], '|')) : $match[1][0];

                if (in_array($var_name, $noparse)) {
                    $text = $this->createExtraction('noparse', $match[0][0], $match[2][0], $text);
                    continue;
                }

                $loop_data = $this->getVariable(trim($match[1][0]), $data);

                if (! $loop_data) {
                    // Must be a callback block. Extract it so it doesn't
                    // conflict with local scope variables in the next step.
                    $text = $this->createExtraction('callback_blocks', $match[0][0], $match[0][0], $text);
                } else {
                    $looped_text = '';
                    $index = 0;

                    // If we're not in a loop, we're done here.
                    if (!is_array($loop_data)) return $loop_data;

                    // is this a list, or simply a set of named variables?
                    if ((bool) count(array_filter(array_keys($loop_data), 'is_string'))) {
                        // this is a set of named variables, don't actually loop over and over,
                        // instead, parse the inner contents with this set's local variables that
                        // have been merged into the bigger scope

                        // merge this local data with callback data before performing actions
                        $loop_value = $loop_data + $data + $this->callbackData;

                        // perform standard actions
                        $str = $this->extractLoopedTags($match[2][0], $loop_value, $callback);
                        $str = $this->parseConditionals($str, $loop_value, $callback);
                        $str = $this->injectExtractions($str, 'looped_tags');
                        $str = $this->parseVariables($str, $loop_value, $callback);

                        if (!is_null($callback)) {
                            $str = $this->injectExtractions($str, 'callback_blocks');
                            $str = $this->parseCallbackTags($str, $loop_value, $callback);
                        }

                        $looped_text .= $str;
                        $text = preg_replace('/' . preg_quote($match[0][0], '/') . '/m', addcslashes($looped_text, '\\$'), $text, 1);
                    } else {
                        // this is a list, let's loop
                        $total_results = count($loop_data);

                        foreach ($loop_data as $loop_key => $loop_value) {
                            $index++;

                            // Terms should be converted to arrays automatically.
                            if ($loop_value instanceof Term) {
                                $loop_value = $loop_value->toArray();
                            }

                            // is the value an array?
                            if (! is_array($loop_value)) {
                                // no, make it one
                                $loop_value = [
                                    'value' => $loop_value,
                                    'name'  => $loop_value // alias for backwards compatibility
                                ];
                            }

                            // set contextual loop vars
                            $loop_value['key']           = $loop_key;
                            $loop_value['index']         = $index;
                            $loop_value['zero_index']    = $index - 1;
                            $loop_value['total_results'] = $total_results;
                            $loop_value['first']         = ($index === 1) ? true : false;
                            $loop_value['last']          = ($index === $loop_value['total_results']) ? true : false;

                            // merge this local data with callback data before performing actions
                            $loop_value = $loop_value + $data + $this->callbackData;

                            // perform standard actions
                            $str = $this->extractLoopedTags($match[2][0], $loop_value, $callback);
                            $str = $this->parseConditionals($str, $loop_value, $callback);
                            $str = $this->injectExtractions($str, 'looped_tags');
                            $str = $this->parseVariables($str, $loop_value, $callback);

                            if (!is_null($callback)) {
                                $str = $this->injectExtractions($str, 'callback_blocks');
                                $str = $this->parseCallbackTags($str, $loop_value, $callback);
                            }

                            $looped_text .= $str;
                        }

                        $text = preg_replace('/' . preg_quote($match[0][0], '/') . '/m', addcslashes($looped_text, '\\$'), $text, 1);
                    }
                }
            }
        }

        return $text;
    }

    /**
     * Look for and parse string variables
     *
     * @param  string       $html      The HTML markup
     * @param  array|object $data      the data
     * @param  callable     $callback  optional callback
     * @return string
     */
    public function parseStringVariables($text, $data, $callback)
    {
        // Check for any vars flagged as noparse
        $noparse = array_get($data, '_noparse', []);

        /**
         * $data_matches[0] is the raw data tag
         * $data_matches[1] is the data variable (dot notated)
         */
        if (preg_match_all($this->variableTagRegex, $text, $data_matches)) {

            // add ability to specify `or` to find matches
            foreach ($data_matches[1] as $index => $var) {

                // check for ` | ` modifier delimiter surrounded by spaces
                if (strpos($var, ' | ') !== false) {
                    $var = str_replace(' | ', '|', $var);
                }

                // check for `or` options
                if (strpos($var, ' or ') !== false) {
                    $vars = preg_split('/\s+or\s+/ms', $var, null, PREG_SPLIT_NO_EMPTY);
                } else {
                    $vars = [$var];
                }

                $size = sizeof($vars);
                for ($i = 0; $i < $size; $i++) {

                    // account for modifiers
                    $var      = trim($vars[$i]);
                    $var_pipe = strpos($var, '|');
                    $var_name = ($var_pipe !== false) ? substr($var, 0, $var_pipe) : $var;

                    if (preg_match('/^(["\']).+\1$/', $var)) {
                        $text = str_replace($data_matches[0][$index], substr($var, 1, strlen($var) - 2), $text);
                        break;
                    }

                    // retrieve the value of $var, otherwise, a no-value string
                    $val = $this->getVariable($var, $data, '__lex_no_value__');

                    // we only want to continue if:
                    //   - $val has no value according to the parser
                    //   - $val *does* have a value, it's falsey, there are multiple options, *and* we're not on the last one
                    if ($val === '__lex_no_value__' || (!$val && $size > 1 && $i < ($size - 1))) {
                        continue;
                    } else {
                        // prevent arrays trying to be printed as a string
                        if (is_array($val)) {
                            $val = null;
                            \Log::error("Cannot render an array variable as a string: {{ $var }}");
                        }

                        // if variable is in the noparse list, extract it.
                        if (($var_pipe !== false && in_array('noparse', array_slice(explode('|', $var), 1))) || in_array($var_name, $noparse)) {
                            $text = $this->createExtraction('noparse', $data_matches[0][$index], $val, $text);
                        } else {
                            if ($val instanceof Term) {
                                $val = $val->slug();
                            }
                            $text = str_replace($data_matches[0][$index], $val, $text);
                        }

                        break;
                    }
                }
            }
        }

        return $text;
    }

    /**
     * Look for and parse variables with parameter style modifiers.
     * Example: {{ date format="Y-m-d" }}
     *
     * @param  string       $html      The HTML markup
     * @param  array|object $data      the data
     * @param  callable     $callback  optional callback
     * @return string
     */
    public function parseVariablesWithParameterStyleModifiers($text, $data, $callback)
    {
        $regex = '/\{\{\s*(' . $this->looseVariableRegex . ')(\s+.*?)?\s*\}\}/ms';

        if (preg_match_all($regex, $text, $data_matches, PREG_SET_ORDER + PREG_OFFSET_CAPTURE)) {
            foreach ($data_matches as $match) {
                // grab some starting values & init variables
                $parameters = [];
                $tag        = $match[0][0];
                $name       = $match[1][0];

                // is this not the content tag, and is the value known?
                if (array_get_colon($data, $name)) {
                    // the value is known. Are there parameters?
                    if (isset($match[2])) {
                        // there are, make a backup of our $data
                        $cb_data = $data;

                        // is $data an array?
                        if (is_array($data)) {
                            // it is, have we had callback data before?
                            if (! empty($this->callbackData)) {
                                // we have, merge it all together
                                $cb_data = $data + $this->callbackData;
                            }

                            // grab the raw string of parameters
                            $raw_params = $this->injectExtractions($match[2][0], '__cond_str');

                            // parse them into an array
                            $parameters = $this->parseParameters($raw_params, $cb_data, $callback);
                        } elseif (is_string($data)) {
                            $text = str_replace($tag, $data, $text);
                        }
                    }

                    // Parameter-style modifier time
                    // Probably should do an extraction here...
                    $replacement = array_get_colon($data, $name);

                    foreach ($parameters as $modifier => $parameters) {
                        $replacement = $this->runModifier($modifier, $replacement, explode('|', $parameters), $data);
                    }

                    $text = str_replace($tag, $replacement, $text);
                }
            }
        }

        return $text;
    }

    /**
     * Parses all Callback tags, and sends them through the given $callback.
     *
     * @param  string $text     Text to parse
     * @param  array  $data     An array of data to use
     * @param  mixed  $callback Callback to apply to each tag
     * @return string
     */
    public function parseCallbackTags($text, $data, $callback)
    {
        $inCondition = $this->inCondition;

        // if there are no instances of a tag, abort
        if (strpos($text, '{') === false) {
            return $text;
        }

        if ($inCondition) {
            $regex = '/\{\{?\s*(' . $this->variableRegex . ')(\s+.*?)?\s*\}\}?/ms';
        } else {
            $regex = '/\{\{\s*(' . $this->variableRegex . ')(\s+.*?)?\s*(\/)?\}\}/ms';
        }

        // Make a clean copy of the collective callback data
        $cb_data = $data;

        /**
         * $match[0][0] is the raw tag
         * $match[0][1] is the offset of raw tag
         * $match[1][0] is the callback name
         * $match[1][1] is the offset of callback name
         * $match[2][0] is the parameters
         * $match[2][1] is the offset of parameters
         * $match[3][0] is the self closure
         * $match[3][1] is the offset of closure
         */
        while (preg_match($regex, $text, $match, PREG_OFFSET_CAPTURE)) {
            // update the collective data if it's different
            if (! empty($this->callbackData)) {
                $cb_data = $data + $this->callbackData;
            }

            $content    = '';
            $parameters = [];
            $tag        = $match[0][0];
            $start      = $match[0][1];
            $name       = $match[1][0];
            $selfClosed = array_get($match, 3, false);
            $text_subselection = substr($text, $start + strlen($tag));

            if (isset($match[2])) {
                $raw_params = $this->injectExtractions($match[2][0], '__cond_str');
                $parameters = $this->parseParameters($raw_params, $cb_data, $callback);
                $parameters = $this->parseVariablesInsideParameters($parameters, $data, $callback);
            }

            if (preg_match('/\{\{\s*\/' . preg_quote($name, '/') . '\s*\}\}/m', $text_subselection, $match, PREG_OFFSET_CAPTURE) && !$selfClosed) {
                $content = substr($text_subselection, 0, $match[0][1]);
                $tag .= $content . $match[0][0];

                // Is there a nested block under this one existing with the same name?
                $nested_regex = '/\{\{\s*(' . preg_quote($name, '/') . ')(\s.*?)\}\}(.*?)\{\{\s*\/\1\s*\}\}/ms';
                if (preg_match($nested_regex, $content . $match[0][0], $nested_matches)) {
                    $nested_content = preg_replace('/\{\{\s*\/' . preg_quote($name, '/') . '\s*\}\}/m', '', $nested_matches[0]);
                    $content = $this->createExtraction('nested_looped_tags', $nested_content, $nested_content, $content);
                }
            }

            $replacement = null; // oh look, another temporary variable.

            // now, check to see if a callback should happen
            if ($callback) {
                $replacement = call_user_func_array($callback, [$name, $parameters, $content, $data]);
                $replacement = $this->parseRecursives($replacement, $content, $callback);
            }

            // look for tag pairs and (plugin) callbacks
            if ($name != "content" && !$replacement) {

                // is the callback a variable in our data set?
                if ($values = array_get_colon($data, $name)) {

                    // is this a tag-pair?
                    if (is_array($values)) {
                        // yes it is
                        // there might be parameters that will control how this
                        // tag-pair's data is filtered/sorted/limited/etc,
                        // look for those and apply those as needed

                        // exact result grabbing ----------------------------------

                        foreach ($parameters as $modifier => $parameters) {
                            $parameters = explode(':', $parameters);
                            $values = $this->runModifier($modifier, $values, $parameters, $data);
                        }

                        // loop over remaining values, adding contextual tags
                        // to each iteration of the loop
                        $i = 0;
                        $total_results = count($values);

                        foreach ($values as $value_key => $value_value) {
                            $i++;

                            // if this isn't an array, we need to make it one
                            if (!is_array($values[$value_key])) {
                                // not an array, set contextual tags
                                // note: these are for tag-pairs only
                                $values[$value_key] = [
                                    'key'           => $i - 1,
                                    'value'         => $values[$value_key],
                                    'name'          => $values[$value_key], // 'value' alias (legacy)
                                    'index'         => $i,
                                    'zero_index'    => $i - 1,
                                    'total_results' => $total_results,
                                    'first'         => ($i === 1) ? true : false,
                                    'last'          => ($i === $total_results) ? true : false
                                ];
                            }
                        }
                    }

                    if ( ! empty($values)) {
                        // parse the tag found with the value(s) related to it
                        $tmpname = md5($name);
                        $vars = [$tmpname => $values];
                        $replacement = $this->parseVariables("{{ $tmpname }}$content{{ /$tmpname }}", [$tmpname => $values], $callback);
                    }
                } else {
                    // nope, this must be a callback
                    if (is_null($callback)) {
                        // TODO: what does this do?
                        $text = $this->createExtraction('__variables_not_callbacks', $text, $text, $text);
                    } elseif (! empty($cb_data[$name])) {
                        // value not found in the data block, so we check the
                        // cumulative callback data block for a value and use that
                        $text = $this->extractLoopedTags($text, $cb_data, $callback);
                        $text = $this->parseVariables($text, $cb_data, $callback);
                        $text = $this->injectExtractions($text, 'callback_blocks');
                    }
                }
            }

            // Variables within conditions can be parsed more than once. We can
            // skip this block if it's already been run through $this->valueToLiteral
            if ($inCondition && (substr($text, 0, 1) !== "'" && substr($text, -1, 1) !== "'")) {
                $replacement = $this->valueToLiteral($replacement);
            }

            $text = preg_replace('/' . preg_quote($tag, '/') . '/m', addcslashes($replacement, '\\$'), $text, 1);
            $text = $this->injectExtractions($text, 'nested_looped_tags');
        }

        // parse for recursives, as they may not have been parsed yet
        $text = $this->parseRecursives($text, $this->original_text, $callback);

        // re-inject any extractions
        if (is_null($callback)) {
            $text = $this->injectExtractions($text, '__variables_not_callbacks');
        }

        return $text;
    }

    /**
     * Parses {variables} with single braces inside parameters
     * making sure the parameter's parameters are an array in order to resolve duplicates.
     */
    public function parseVariablesInsideParameters($parameters, $data, $callback)
    {
        return collect($parameters)->map(function ($value) use ($data, $callback) {
            preg_match_all('/(\{\s*' . $this->variableRegex . '\s*\})/', $value, $matches);

            $value = str_replace(['{', '}'], ['{{', '}}'], $value);
            $value = $this->parseVariables($value, $data);

            return $this->parseCallbackTags($value, $data, $callback);
        })->toArray();
    }

    /**
     * Parses all conditionals, then executes the conditionals.
     *
     * @param  string $text     Text to parse
     * @param  mixed  $data     Data to use when executing conditionals
     * @param  mixed  $callback The callback to be used for tags
     * @return string
     */
    public function parseConditionals($text, $data, $callback)
    {
        preg_match_all($this->conditionalRegex, $text, $matches, PREG_SET_ORDER);

        $this->conditionalData = $data;

        /**
         * $matches[][0] = Full Match
         * $matches[][1] = Either 'if', 'unless', 'elseif', 'elseunless'
         * $matches[][2] = Condition
         */
        foreach ($matches as $match) {
            $this->inCondition = true;

            $condition = $match[2];

            if (strpos($condition, ' | ') !== false) {
                $condition = str_replace(' | ', '|', $condition);
            }

            // check for and extract callbacks
            if ($callback) {
                if (preg_match_all('/\b(?!\{\s*)(' . $this->callbackNameRegex . ')(?!\s+.*?\s*\})\b/', $condition, $cb_matches)) {
                    foreach ($cb_matches[0] as $m) {
                        $condition = $this->createExtraction('__cond_callbacks', $m, "{$m}", $condition);
                    }
                }
            }

            // Extract all literal strings in the conditional to simplify
            if (preg_match_all('/(["\']).*?(?<!\\\\)\1/', $condition, $str_matches)) {
                foreach ($str_matches[0] as $m) {
                    $condition = $this->createExtraction('__cond_str', $m, $m, $condition);
                }
            }

            $condition = preg_replace($this->conditionalNotRegex, '$1!$2', $condition);

            if (preg_match_all($this->conditionalExistsRegex, $condition, $existsMatches, PREG_SET_ORDER)) {
                foreach ($existsMatches as $m) {
                    $exists = 'true';
                    if ($this->getVariable($m[2], $data, '__doesnt_exist__') === '__doesnt_exist__') {
                        $exists = 'false';
                    }
                    $condition = $this->createExtraction('__cond_exists', $m[0], $m[1] . $exists . $m[3], $condition);
                }
            }

            // replaced a static-ish call to a callback with an anonymous function so that we could
            // also pass in the current callback (for later processing callback tags); also setting
            // $ref so that we can use it within the anonymous function
            $ref = $this;
            $condition = preg_replace_callback('/\b(' . $this->variableRegex . ')\b/', function ($match) use ($callback, $ref) {
                return $ref->processConditionVar($match, $callback);
            }, $condition);

            // inject and parse any callbacks
            if ($callback) {
                $condition = $this->injectExtractions($condition, '__cond_callbacks');
                $condition = $this->parseCallbackTags($condition, $data, $callback, true);
            }

            // Re-extract the strings that have may have been added.
            if (preg_match_all('/(["\']).*?(?<!\\\\)\1/s', $condition, $str_matches)) {
                foreach ($str_matches[0] as $m) {
                    $condition = $this->createExtraction('__cond_str', $m, $m, $condition);
                }
            }

            // Re-process variables by tricking processConditionVar
            $this->inCondition = false;

            // replacements -- the preg_replace_callback below is using word boundaries, which
            // will break when one of your original variables gets replaced with a URL path
            // (because word boundaries think slashes are boundaries) -- to fix this, we replace
            // all instances of a literal string in single quotes with a temporary replacement
            $replacements = [];

            // Literally replace literal strings
            while (preg_match("/('[^']+'|\"[^\"]+\")/", $condition, $replacement_matches)) {
                $replacement_match = $replacement_matches[1];
                $replacement_hash = md5($replacement_match);

                $replacements[$replacement_hash] = $replacement_match;
                $condition = str_replace($replacement_match, "__temp_replacement_" . $replacement_hash, $condition);
            }

            // next, the original re-processing callback
            $correct_regex = (strpos($condition, '(') === 0) ? $this->looseVariableRegex : $this->variableRegex;

            $condition = preg_replace_callback('/\b(' . $correct_regex . ')\b/', [$this, 'processConditionVar'], $condition);

            // finally, replacing our placeholders with the original values
            foreach ($replacements as $replace_key => $replace_value) {
                $condition = str_replace('__temp_replacement_' . $replace_key, $replace_value, $condition);
            }

            $this->inCondition = true;

            // evaluate special comparisons
            if (strpos($condition, ' ~ ') !== false) {
                $new_condition = preg_replace_callback('/(.*?)\s*~\s*(__cond_str_[a-f0-9]{32})/', function ($cond_matches) {
                    return 'preg_match(' . $cond_matches[2] . ', ' . $cond_matches[1] . ', $temp_matches)';
                }, $condition);

                if ($new_condition !== false) {
                    $condition = $new_condition;
                }
            }

            // Re-inject any strings we extracted
            $condition = $this->injectExtractions($condition, '__cond_str');
            $condition = $this->injectExtractions($condition, '__cond_exists');

            $conditional = '<?php ';

            if ($match[1] == 'unless') {
                $conditional .= 'if ( ! (' . $condition . '))';
            } elseif ($match[1] == 'elseunless') {
                $conditional .= 'elseif ( ! (' . $condition . '))';
            } else {
                $conditional .= $match[1] . ' (' . $condition . ')';
            }

            $conditional .= ': ?>';

            $text = preg_replace('/' . preg_quote($match[0], '/') . '/m', addcslashes($conditional, '\\$'), $text, 1);
        }

        $text = preg_replace($this->conditionalElseRegex, '<?php else: ?>', $text);
        $text = preg_replace($this->conditionalEndRegex, '<?php endif; ?>', $text);

        $text = $this->parsePhp($text);
        $this->inCondition = false;

        return $text;
    }

    /**
     * Recursively process a callback tag with a passed child array.
     *
     * @param  string $text       The replaced text after a callback.
     * @param  string $orig_text  The original text, before a callback is called.
     * @param  mixed  $callback
     * @return string $text
     */
    public function parseRecursives($text, $orig_text, $callback)
    {
        // Is there a {{ *recursive [array_key]* }} tag here, let's loop through it.
        if (preg_match($this->recursiveRegex, $text, $match)) {
            $tag = $match[0];
            $array_key = $match[1];

            // check to see if the recursive variable we're looking for is set
            // within the current data for this run-through, if it isn't, just
            // abort and return the text
            if (!array_get_colon($this->callbackData, $array_key)) {
                return $text;
            }

            $next_tag = null;
            $children = array_get_colon($this->callbackData, $array_key);

            // if the array key is scoped, we'll add a scope to the array
            if (strpos($array_key, ':') !== false) {
                $scope = explode(':', $array_key)[0];
                $children = Arr::addScope($children, $scope);
            }

            $child_count = count($children);
            $count = 1;

            // Let's make sure it's multi-dimensional.
            if ($child_count == count($children, COUNT_RECURSIVE)) {
                $children = [$children];
                $child_count = 1;
            }

            foreach ($children as $child) {
                $has_children = true;

                // If this is a object let's convert it to an array.
                is_array($child) or $child = (array) $child;

                // Does this child not contain any children?
                // Let's set it as empty then to avoid any errors.
                if (! array_key_exists($array_key, $child)) {
                    $child[$array_key] = [];
                    $has_children      = false;
                }

                $replacement = $this->parse($orig_text, $child, $callback, $this->allowPhp);

                // If this is the first loop we'll use $tag as reference, if not
                // we'll use the previous tag ($next_tag)
                $current_tag = ($next_tag !== null) ? $next_tag : $tag;

                // If this is the last loop set the next tag to be empty
                // otherwise hash it.
                $next_tag = ($count == $child_count) ? '' : md5($tag . $replacement);

                $text = str_replace($current_tag, $replacement . $next_tag, $text);

                if ($has_children) {
                    $text = $this->parseRecursives($text, $orig_text, $callback);
                }

                $count++;
            }
        }

        return $text;
    }

    /**
     * Set the noparse style. Immediate or cumulative.
     *
     * @param  bool $mode
     * @return void
     */
    public function cumulativeNoparse($mode)
    {
        $this->cumulativeNoparse = $mode;
    }

    /**
     * Injects noparse extractions.
     *
     * This is so that multiple parses can store noparse
     * extractions and all noparse can then be injected right
     * before data is displayed.
     *
     * @param  string $text Text to inject into
     * @return string
     */
    public static function injectNoparse($text)
    {
        if (isset(self::$extractions['noparse'])) {
            foreach (self::$extractions['noparse'] as $hash => $replacement) {
                if (strpos($text, "noparse_{$hash}") !== false) {
                    $text = str_replace("noparse_{$hash}", $replacement, $text);
                }
            }
        }

        return $text;
    }

    /**
     * This is used as a callback for the conditional parser.  It takes a variable
     * and returns the value of it, properly formatted.
     *
     * @param  array    $match    A match from preg_replace_callback
     * @param  callable $callback A callback to use to process further callback tags
     * @return string
     */
    public function processConditionVar($match, $callback = null)
    {
        $var = is_array($match) ? $match[0] : $match;

        if (in_array(strtolower($var), array('true', 'false', 'null', 'or', 'and')) or
            strpos($var, '__cond_str') === 0 or
            strpos($var, '__cond_exists') === 0 or

            // adds a new temporary replacement to deal with string literals
            strpos($var, '__temp_replacement') === 0 or

            is_numeric($var)
        ) {
            return $var;
        }

        $value = $this->getVariable($var, $this->conditionalData, '__processConditionVar__');

        // if the resulting value of a variable is a string that contains another variable,
        // let's find that variable's value as well
        if (!is_array($value)) {
            if ($value instanceof Term) {
                $value = $value->slug();
            }

            while (preg_match($this->variableTagRegex, $value, $matches)) {
                $previous_value = $value;
                $value = $this->parseVariables($value, $this->conditionalData, $callback);

                // nothing changed, break out, prevents any sort of infinite looping
                if ($previous_value === $value) {
                    break;
                }
            }
        }

        if ($value === '__processConditionVar__') {
            return $this->inCondition ? $var : 'null';
        }

        return $this->valueToLiteral($value);
    }

    /**
     * This is used as a callback for the conditional parser.  It takes a variable
     * and returns the value of it, properly formatted.
     *
     * @param  array $match A match from preg_replace_callback
     * @return string
     */
    protected function processParamVar($match)
    {
        return $match[1] . $this->processConditionVar($match[2]);
    }

    /**
     * Takes a value and returns the literal value for it for use in a tag.
     *
     * @param  string $value Value to convert
     * @return string
     */
    protected function valueToLiteral($value)
    {
        if (is_object($value) and is_callable(array($value, '__toString'))) {
            return var_export((string)$value, true);
        } elseif (is_array($value)) {
            return !empty($value) ? "true" : "false";
        } else {
            return var_export($value, true);
        }
    }

    /**
     * Ignore tags-who-must-not-be-parsed
     *
     * @param  string $text The text to extract from
     * @return string
     */
    protected function extractNoparse($text)
    {
        // Ignore @{{ tags }} so we don't have to write JavaScript like animals.
        if (preg_match_all($this->ignoreRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = $this->createExtraction('noparse', $match[0], ltrim($match[0], '@'), $text);
            }
        }

        /**
         * $matches[][0] is the raw noparse match
         * $matches[][1] is the noparse contents
         */
        if (preg_match_all($this->noparseRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = $this->createExtraction('noparse', $match[0], $match[1], $text);
            }
        }

        return $text;
    }

    /**
     * Extracts the looped tags so that we can parse conditionals then re-inject.
     *
     * @param string   $text     The text to extract from
     * @param array    $data     Data array to use
     * @param callable $callback Callback to call when complete
     * @return string
     */
    protected function extractLoopedTags($text, $data = array(), $callback = null)
    {
        /**
         * $matches[][0] is the raw match
         */
        if (preg_match_all($this->callbackBlockRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {

                // Allow {{ /if }} to close if statements
                if ($match[1] === 'if' || $match[1] === 'unless') {
                    // move on
                } elseif ($this->parseParameters($match[2], $data, $callback)) {
                    // This callback block contains parameters
                    // Let's extract it so it doesn't conflict with local variables when
                    // parseVariables() is called.
                    $text = $this->createExtraction('callback_blocks', $match[0], $match[0], $text);
                } else {
                    $text = $this->createExtraction('looped_tags', $match[0], $match[0], $text);
                }
            }
        }

        return $text;
    }

    /**
     * Extracts text out of the given text and replaces it with a hash which
     * can be used to inject the extractions replacement later.
     *
     * @param  string $type        Type of extraction
     * @param  string $extraction  The text to extract
     * @param  string $replacement Text that will replace the extraction when re-injected
     * @param  string $text        Text to extract out of
     * @return string
     */
    protected function createExtraction($type, $extraction, $replacement, $text)
    {
        $hash = md5($replacement);
        self::$extractions[$type][$hash] = $replacement;

        return str_replace($extraction, "{$type}_{$hash}", $text);
    }

    /**
     * Injects all of the extractions.
     *
     * @param string $text Text to inject into
     * @param string $type Type of extraction to inject
     * @return string
     */
    protected function injectExtractions($text, $type = null)
    {
        if (is_null($type)) {
            foreach (self::$extractions as $type => $extractions) {
                foreach ($extractions as $hash => $replacement) {
                    if (strpos($text, "{$type}_{$hash}") !== false) {
                        $text = str_replace("{$type}_{$hash}", $replacement, $text);
                        unset(self::$extractions[$type][$hash]);
                    }
                }
            }
        } else {
            if (! isset(self::$extractions[$type])) {
                return $text;
            }

            foreach (self::$extractions[$type] as $hash => $replacement) {
                if (strpos($text, "{$type}_{$hash}") !== false) {
                    $text = str_replace("{$type}_{$hash}", $replacement, $text);
                    unset(self::$extractions[$type][$hash]);
                }
            }
        }

        return $text;
    }


    /**
     * Takes a scope-notated key and finds the value for it in the given
     * array or object.
     *
     * @param  string       $key     Dot-notated key to find
     * @param  array|object $data    Array or object to search
     * @param  mixed        $default Default value to use if not found
     * @return mixed
     */
    protected function getVariable($key, $data, $default = null)
    {
        $context = $data;

        list($key, $modifiers) = $this->parseModifiers($key);

        if (strpos($key, ':') === false) {
            $parts = explode('.', $key);
        } else {
            $parts = explode(':', $key);
        }

        foreach ($parts as $key_part) {
            if (is_array($data)) {
                if (! array_key_exists($key_part, $data)) {
                    return $default;
                }

                $data = $data[$key_part];
            } elseif (is_object($data)) {
                if (! isset($data->{$key_part})) {
                    return $default;
                }

                $data = $data->{$key_part};
            } else {
                return $default;
            }
        }

        // execute the modifier chain
        if ($modifiers) {
            foreach ($modifiers as $modifier) {
                $modifier_bits = explode(':', $modifier);
                $data = $this->runModifier(array_get_colon($modifier_bits, 0), $data, array_slice($modifier_bits, 1), $context);
            }
        }

        // convert collections to arrays
        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->toArray();
        }

        return $data;
    }

    /**
     * Evaluates the PHP in the given string.
     *
     * @param string $text Text to evaluate
     * @return string
     * @throws \Statamic\Exceptions\ParsingException
     */
    protected function parsePhp($text)
    {
        ob_start();

        $result = eval('?>' . $text . '<?php ');

        if ($result === false) {
            $output = 'You have a syntax error in your Lex tags. The offending code: ';
            throw new ParsingException($output . str_replace(array('?>', '<?php '), '', $text));
        }

        return ob_get_clean();
    }

    /**
     * Parses a parameter string into an array
     *
     * @param string   $parameters The string of parameters
     * @param array    $data       Array of data
     * @param callable $callback   Callback function to call
     * @return array
     */
    protected function parseParameters($parameters, $data, $callback)
    {
        $this->conditionalData = $data;
        $this->inCondition = true;

        // Extract all literal strings in the conditional to simplify
        if (preg_match_all('/(["\']).*?(?<!\\\\)\1/', $parameters, $str_matches)) {
            foreach ($str_matches[0] as $m) {
                $parameters = $this->createExtraction('__param_str', $m, $m, $parameters);
            }
        }

        $parameters = preg_replace_callback(
            '/(.*?\s*=\s*(?!__))('.$this->variableRegex.')/is',
            [$this, 'processParamVar'],
            $parameters
        );

        if ($callback) {
            $parameters = preg_replace('/(.*?\s*=\s*(?!\{\s*)(?!__))('.$this->callbackNameRegex.')(?!\s*\})\b/', '$1{$2}', $parameters);
            $parameters = $this->parseCallbackTags($parameters, $data, $callback);
        }

        // Re-inject extracted strings
        $parameters = $this->injectExtractions($parameters, '__param_str');
        $this->inCondition = false;

        if (preg_match_all('/(.*?)\s*=\s*(\'|"|&#?\w+;)(.*?)(?<!\\\\)\2/s', trim($parameters), $matches)) {
            $return = [];

            foreach ($matches[1] as $i => $attr) {
                // if there are duplicate parameters, save an array instead of a string
                $key = trim($matches[1][$i]);
                $value = stripslashes($matches[3][$i]);

                // if it already exists (a parameter with this name already parsed)
                if (isset($return[$key])) {
                    // if its not already an array, turn it into one
                    if (! is_array($return[$key])) {
                        $return[$key] = [$return[$key]];
                    }

                    $return[$key][] = $value;

                } else {
                    // parameter hasnt been parsed yet. just save a string as usual
                    $return[$key] = $value;
                }
            }

            return $return;
        }

        return [];
    }

    protected function parseModifiers($key)
    {
        $parts = explode("|", $key);

        return [
            array_get_colon($parts, 0),
            (array) array_slice($parts, 1)
        ];
    }

    /**
     * Manipulate data with the use of Modifiers
     *
     * @param $modifier
     * @param $data
     * @param $parameters
     * @param $context
     * @return mixed
     */
    protected function runModifier($modifier, $data, $parameters, $context = [])
    {
        try {
            return Modify::value($data)->context($context)->$modifier($parameters)->fetch();

        } catch (ModifierException $e) {
            \Log::notice(
                sprintf('Error in [%s] modifier: %s', $e->getModifier(), $e->getMessage())
            );

            return $data;
        }
    }
}
