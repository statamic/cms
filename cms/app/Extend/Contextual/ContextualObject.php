<?php

namespace Statamic\Extend\Contextual;

class ContextualObject
{
    /**
     * The "context" of the object. The addon name.
     *
     * @protected string
     */
    protected $context;

    /**
     * Create a new contextual addon object
     *
     * @param  \Statamic\Extend\Extensible|string  $context
     */
    public function __construct($context)
    {
        if (is_object($context)) {
            $context = $context->getAddonClassName();
        }

        $this->context = $context;
    }

    /**
     * Returns a value prepended by the context
     *
     * @param string $value
     * @return string
     */
    protected function contextualize($value)
    {
        return 'addons:' . $this->context . ':' . $value;
    }
}
