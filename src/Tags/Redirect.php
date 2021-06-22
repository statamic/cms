<?php

namespace Statamic\Tags;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Fields\Value;

class Redirect extends Tags
{
    public function wildcard($tag)
    {
        return $this->redirect(
            $this->context->value($tag)
        );
    }

    public function index()
    {
        if ($route = $this->params->get('route')) {
            return $this->redirect(route(
                $route,
                $this->params->forget('route')->all()
            ));
        }

        return $this->redirect(
            $this->params->get(['to', 'url'])
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

        abort(redirect($location, $this->params->get('response', 302)));
    }
}
