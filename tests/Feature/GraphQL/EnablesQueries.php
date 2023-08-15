<?php

namespace Tests\Feature\GraphQL;

use Statamic\Support\Arr;

trait EnablesQueries
{
    public function getEnvironmentSetup($app)
    {
        parent::getEnvironmentSetUp($app);

        if ($this->enabledQueries) {
            foreach (Arr::wrap($this->enabledQueries) as $key) {
                $app['config']->set('statamic.graphql.resources.'.$key, true);
            }
        }
    }
}
