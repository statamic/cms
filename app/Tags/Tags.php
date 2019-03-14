<?php

namespace Statamic\Tags;

use Statamic\API\Arr;
use Statamic\API\Str;
use Statamic\API\Parse;
use Statamic\API\Antlers;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasAliases;
use Statamic\Extend\HasContext;
use Statamic\Data\DataCollection;
use Statamic\Extend\HasParameters;
use Statamic\Extend\RegistersItself;

abstract class Tags
{
    use HasHandle, HasAliases, HasParameters, HasContext, RegistersItself;

    protected static $binding = 'tags';

    /**
     * The content written between the tags (when a tag pair)
     * @public string
     */
    public $content;

    /**
     * The variable context around which this tag is positioned
     * @public array
     */
    public $context;

    /**
     * The tag that was used
     *
     * eg. For {{ ron:swanson foo="bar" }}, this would be `ron:swanson`
     *     and for {{ ron foo="bar" }} it would be `ron:index`
     *
     * @var string
     */
    public $tag;

    /**
     * The tag method that was used
     *
     * eg. For {{ ron:swanson foo="bar" }}, this would be `swanson`
     *     and for {{ ron foo="bar" }}, it would `index`
     *
     * @var string
     */
    public $method;

    /**
     * If is a tag pair
     * @var bool
     */
    public $isPair;

    /**
     * Whether to trim the whitespace from the content before parsing
     * @var  bool
     */
    protected $trim = false;

    /**
     * The parser instance that executed this tag.
     * @var \Statamic\View\Antlers\Parser
     */
    public $parser;

    public function setProperties($properties)
    {
        $this->parser      = $properties['parser'];
        $this->content     = $properties['content'];
        $this->context     = $properties['context'];
        $this->parameters  = $this->setUpParameters($properties['parameters']);
        $this->isPair      = $this->content !== '';
        $this->tag         = array_get($properties, 'tag');
        $this->method      = array_get($properties, 'tag_method');
    }

    /**
     * Perform set-up on any parameters
     *
     * @param array $params
     * @return array
     */
    private function setUpParameters($params)
    {
        foreach ($params as $param => $value) {
            // Values in parameters prefixed with a colon should be treated as the corresponding
            // field's value in the context. If it doesn't exist, the value remains the literal.
            if (Str::startsWith($param, ':')) {
                $params[substr($param, 1)] = array_get_colon($this->context, $value, $value);
                unset($params[$param]);
            }

            if ($value === 'true') {
                $params[$param] = true;
            }

            if ($value === 'false') {
                $params[$param] = false;
            }
        }

        return $params;
    }

    /**
     * Trim the content
     *
     * @param   bool    $trim  Whether to trim the content
     * @return  $this
     */
    protected function trim($trim = true)
    {
        $this->trim = $trim;

        return $this;
    }

    /**
     * Parse the tag pair contents with scoped variables
     *
     * @param array $data     Data to be parsed into template
     * @return string
     */
    public function parse($data = [])
    {
        if ($this->trim) {
            $this->content = trim($this->content);
        }

        $variables = array_merge($this->context, $this->addScope($data));

        return Antlers::usingParser($this->parser, function ($antlers) use ($variables) {
            return $antlers->parse($this->content, $variables);
        });
    }

    /**
     * Iterate over the data and parse the tag pair contents for each, with scoped variables
     *
     * @param array|\Statamic\Data\DataCollection $data        Data to iterate over
     * @param bool                                $supplement  Whether to supplement with contextual values
     * @return string
     */
    public function parseLoop($data, $supplement = true)
    {
        if ($this->trim) {
            $this->content = trim($this->content);
        }

        return Antlers::usingParser($this->parser, function ($antlers) use ($data, $supplement) {
            return $antlers->parseLoop($this->content, $this->addScope($data), $supplement, $this->context);
        });
    }

    /**
     * Parse with no results
     *
     * @param array $data Extra data to merge
     * @return string
     */
    public function parseNoResults($data = [])
    {
        return $this->parse(array_merge($data, [
            'no_results' => true,
            'total_results' => 0
        ]));
    }

    /**
     * Add the provided $data to its own scope
     *
     * @param array|\Statamic\Data\DataCollection $data
     * @return mixed
     */
    private function addScope($data)
    {
        if ($scope = $this->getParam('scope')) {
            $data = Arr::addScope($data, $scope);
        }

        if ($data instanceof DataCollection) {
            $data = $data->toArray();
        }

        return $data;
    }

    /**
     * Open a form tag
     *
     * @param  string $action
     * @return string
     */
    protected function formOpen($action)
    {
        $attr_str = '';
        if ($attrs = $this->getList('attr')) {
            foreach ($attrs as $attr) {
                $bits = explode(':', $attr);

                $param = array_get($bits, 0);
                $value = array_get($bits, 1);

                $attr_str .= $param;

                if ($value) {
                    $attr_str .= '="' . $value . '" ';
                }
            }
        }

        if ($this->getBool('files')) {
            $attr_str .= 'enctype="multipart/form-data"';
        }

        $html = '<form method="POST" action="'.$action.'" '.$attr_str.'>'.csrf_field();

        return $html;
    }
}
