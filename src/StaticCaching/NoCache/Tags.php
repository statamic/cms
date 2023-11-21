<?php

namespace Statamic\StaticCaching\NoCache;

use Statamic\Facades\Antlers;

class Tags extends \Statamic\Tags\Tags
{
    public static $handle = 'nocache';
    public static $stack = 0;

    /**
     * @var Session
     */
    private $nocache;

    public function __construct(Session $nocache)
    {
        $this->nocache = $nocache;
    }

    public function index()
    {
        if ($this->params->has('select')) {
            $fields = $this->params->explode('select');
        } elseif (config('statamic.antlers.version') === 'runtime') {
            $fields = Antlers::identifiers($this->content);
        }

        return $this
            ->nocache
            ->pushRegion($this->content, $this->context->only($fields ?? null)->all(), 'antlers.html')
            ->placeholder();
    }
}
