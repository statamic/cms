<?php

namespace Statamic\Tags;

use Statamic\Facades\Arr;
use Statamic\Facades\Str;
use Statamic\Facades\Parse;
use Statamic\Facades\Antlers;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasAliases;
use Statamic\Data\DataCollection;
use Statamic\Extend\HasParameters;
use Statamic\Extend\RegistersItself;

abstract class Tags
{
    use HasHandle, HasAliases, HasParameters, RegistersItself;

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
     * The parameters used on this tag.
     * @public array
     */
    public $params;

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

    /**
     * The method that will handle wildcard tags.
     * @var string
     */
    protected $wildcardMethod = 'wildcard';

    /**
     * Whether a wildcard method has already been handled.
     * @var bool
     */
    protected $wildcardHandled;

    public function setProperties($properties)
    {
        $this->setParser($properties['parser']);
        $this->setContent($properties['content']);
        $this->setContext($properties['context']);
        $this->setParameters($properties['parameters']);
        $this->tag         = array_get($properties, 'tag');
        $this->method      = array_get($properties, 'tag_method');
    }

    public function setParser($parser)
    {
        $this->parser = $parser;

        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        $this->isPair = $content !== '';

        return $this;
    }

    public function setContext($context)
    {
        $this->context = new Context($context, $this->parser);

        return $this;
    }

    public function setParameters($parameters)
    {
        $this->params = new Parameters($parameters, $this->context);

        // Temporary BC alias.
        // TODO: Remove with HasParameters trait
        $this->parameters = $this->params;

        return $this;
    }

    /**
     * Handle missing methods.
     *
     * If classes want to provide a catch-all tag, they should add a `wildcard` method.
     */
    public function __call($method, $args)
    {
        if ($this->wildcardHandled || ! method_exists($this, $this->wildcardMethod)) {
            throw new \BadMethodCallException("Call to undefined method {$method}.");
        }

        $this->wildcardHandled = true;

        return $this->{$this->wildcardMethod}($this->method);
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

        $variables = $this->addScope($data); // todo: get rid of scope, but its not under test yet.

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
            return $antlers->parseLoop($this->content, $this->addScope($data), $supplement);
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
