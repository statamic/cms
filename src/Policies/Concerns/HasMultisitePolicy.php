<?php

namespace Statamic\Policies\Concerns;

use Statamic\Facades\Site;

trait HasMultisitePolicy
{
    private function siteIsForbidden($user, ...$arguments)
    {
        if (! Site::hasMultiple()) {
            return false;
        }

        return $this->dataIsInForbiddenSite($user, $arguments)
            || $this->dataHasNoAuthorizedSite($user, $arguments);
    }

    private function dataIsInForbiddenSite($user, $arguments)
    {
        if (! $data = $this->getDataFromArguments($arguments)) {
            return false;
        }

        if (! method_exists($data, 'site')) {
            return false;
        }

        return $user->cant('view', $data->site());
    }

    private function dataHasNoAuthorizedSite($user, $arguments)
    {
        if (! $data = $this->getDataFromArguments($arguments)) {
            return false;
        }

        if (! method_exists($data, 'sites')) {
            return false;
        }

        return $data->sites()
            ->map(fn ($site) => Site::get($site))
            ->filter(fn ($site) => $user->can('view', $site))
            ->isEmpty();
    }

    private function getDataFromArguments($arguments)
    {
        // If this first argument is a string, that means they are passing a class name
        // to the policy. We will remove the first argument from this argument array.
        if (isset($arguments[0]) && is_string($arguments[0])) {
            array_shift($arguments);
        }

        return $arguments[0] ?? null;
    }
}
