<?php

namespace Statamic\StaticCaching;

use Symfony\Component\HttpFoundation\Response;

class ResponseReplacer
{
    /**
     * @var string[]
     */
    protected $replacers = [];

    public function __construct($replacers)
    {
        $this->replacers = $replacers;
    }

    public function prepareForCache(Response $response)
    {
        foreach ($this->replacers as $replacer) {
            $instance = app($replacer);

            if (! ($instance instanceof Replacer)) {
                continue;
            }

            $instance->prepareForCache($response);
        }
    }

    public function replaceInResponse(Response $response)
    {
        foreach ($this->replacers as $replacer) {
            $instance = app($replacer);

            if (! ($instance instanceof Replacer)) {
                continue;
            }

            $instance->replaceInResponse($response);
        }
    }
}