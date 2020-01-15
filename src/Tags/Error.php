<?php

namespace Jonassiewertsen\StatamicButik\Http\Tags;

use Statamic\Tags\Tags;

class Error extends Tags
{
    /**
     * The error bag
     */
    protected $bag;

    public function all() {
        $this->bag = $this->getParam('bag') ?? 'default';

        // A better output is needed
        return $this->getErrorBag();
    }

    /**
     * The {{ error:fieldname }} will return the first error of the fiel
     * If needed, another error bag can be choosen. Add a bag
     * parameter {{ error:fieldname bag="yourform" }}
     */
    public function wildcard($tag)
    {
        $this->bag = $this->getParam('bag') ?? 'default';

        if (!$this->hasErrors()) {
            return false;
        }

        return $this->getFirstError($tag);
    }

    /**
     * Does this form have errors?
     *
     * @return bool
     */
    private function hasErrors()
    {
        return (session()->has('errors'))
            ? session('errors')->hasBag($this->bag)
            : false;
    }

    /**
     * Get the errorBag from session
     *
     * @return object
     */
    private function getErrorBag()
    {
        if ($this->hasErrors()) {
            return session('errors')->getBag($this->bag);
        }
    }

    /**
     * Will return the first error from the tag
     * @param $tag
     * @return string
     */
    private function getFirstError($tag) {
        $errors = $this->getErrorBag()->messages();

        if (! isset($errors[$tag][0])) {
            return false;
        }

        return $errors[$tag][0];
    }
}
