<?php

namespace Statamic\GraphQL\ResponseCache;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\Parser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\GraphQL\ResponseCache;
use Statamic\Events\Event;

class DefaultCache implements ResponseCache
{
    public function get(Request $request)
    {
        if ($this->shouldBypassCache($request->input('query'))) {
            return null;
        }

        return Cache::get($this->getCacheKey($request));
    }

    public function put(Request $request, $response)
    {
        $key = $this->track($request);

        $ttl = Carbon::now()->addMinutes((int) config('statamic.graphql.cache.expiry', 60));

        Cache::put($key, $response, $ttl);
    }

    protected function track($request)
    {
        $newKey = $this->getCacheKey($request);

        $keys = $this
            ->getTrackedResponses()
            ->push($newKey)
            ->unique()
            ->values()
            ->all();

        Cache::put($this->getTrackingKey(), $keys);

        return $newKey;
    }

    protected function getTrackedResponses()
    {
        return collect(Cache::get($this->getTrackingKey(), []));
    }

    protected function getTrackingKey()
    {
        return 'gql-cache:tracked-responses';
    }

    protected function getCacheKey(Request $request)
    {
        $query = $request->input('query');
        $vars = $request->input('variables');

        return 'gql-cache:'.md5($query).'_'.md5(json_encode($vars));
    }

    public function handleInvalidationEvent(Event $event)
    {
        $this->getTrackedResponses()->each(function ($key) {
            Cache::forget($key);
        });

        Cache::forget($this->getTrackingKey());
    }

    public function shouldBypassCache(string $query): bool
    {
        $excludedQueries = config('statamic.graphql.cache.exclude', []);
        if (!$excludedQueries) {
            return false;
        }

        $ast = Parser::parse($query);

        foreach ($ast->definitions as $definition) {
            if (!isset($definition->selectionSet)) {
                continue;
            }

            foreach (array_keys($excludedQueries) as $excludedQuery) {
                foreach ($definition->selectionSet->selections as $selection) {
                    if ($this->containsFieldRecursive($selection, $excludedQuery)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function containsFieldRecursive($node, string $fieldName): bool
    {
        if ($node instanceof FieldNode) {
            if ($node->name->value === $fieldName) {
                return true;
            }

            if ($node->selectionSet) {
                foreach ($node->selectionSet->selections as $selection) {
                    if ($this->containsFieldRecursive($selection, $fieldName)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
