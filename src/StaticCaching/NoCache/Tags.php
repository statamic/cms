<?php

namespace Statamic\StaticCaching\NoCache;

use Statamic\Facades\Antlers;
use Statamic\StaticCaching\Middleware\Cache;

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
        if (! Cache::isBeingUsedOnCurrentRoute()) {
            return $this->parse();
        }

        if ($this->params->has('select')) {
            $fields = $this->params->explode('select');

            if (in_array('@auto', $fields)) {
                $identifiers = Antlers::identifiers($this->content);
                $fields = array_merge(array_diff($fields, ['@auto']), $identifiers);
            }
        }

        return $this
            ->nocache
            ->pushRegion($this->content, $this->context->only($fields ?? null)->all(), 'antlers.html')
            ->placeholder();
    }
}
