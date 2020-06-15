<?php

namespace Statamic\Tags;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Fields\Value;

class Redirect extends Tags
{
    public function wildcard($tag)
    {
        return $this->redirect(
            $this->context->get($tag)
        );
    }

    public function index()
    {
        return $this->redirect(
            $this->get(['to', 'url'])
        );
    }

    protected function redirect($location)
    {
        if ($location instanceof Value) {
            $location = $location->value();
        }

        if ($location === 404) {
            throw new NotFoundHttpException;
        }

        if (! $location) {
            return;
        }

        abort(redirect($location, $this->get('response', 302)));
    }
}
