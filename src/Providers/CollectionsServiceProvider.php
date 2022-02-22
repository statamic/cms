<?php

namespace Statamic\Providers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Statamic\Contracts\Data\Augmentable;

class CollectionsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->getOrPut();
        $this->keyByWithKey();
        $this->l10n();
        $this->pipe();
        $this->transpose();
        $this->toAugmentedArray();
    }

    /**
     * Get a key from a collection if it exists,
     * otherwise put a value in there and return it.
     *
     * @return void
     */
    private function getOrPut()
    {
        Collection::macro('getOrPut', function ($key, $put) {
            if ($this->has($key)) {
                return $this->get($key);
            }

            $this->put($key, $put);

            return $put;
        });
    }

    /**
     * The Laravel 5.3 way of doing keyBy.
     *
     * 5.1 doesn't give us access to the keys.
     *
     * @return void
     */
    private function keyByWithKey()
    {
        Collection::macro('keyByWithKey', function ($keyBy) {
            $keyBy = $this->valueRetriever($keyBy);

            $results = [];

            foreach ($this->items as $key => $item) {
                $resolvedKey = $keyBy($item, $key);

                if (is_object($resolvedKey)) {
                    $resolvedKey = (string) $resolvedKey;
                }

                $results[$resolvedKey] = $item;
            }

            return new static($results);
        });
    }

    private function l10n()
    {
        /*
         * Extract the translations from the files and transform them for
         * usage for the javascript helper.
         *
         * @param  string  $locale
         * @param  string  $prefix  This is for prefixing the keys for our addons.
         */
        Collection::macro('localize', function ($prefix = null) {
            return collect($this->items)
                ->filter(function ($item) {
                    return pathinfo($item, PATHINFO_EXTENSION) == 'php';
                })
                ->keyBy(function ($item) use ($prefix) {
                    return $prefix.pathinfo($item, PATHINFO_FILENAME);
                })
                ->map(function ($item) {
                    return require base_path($item);
                });
        });
    }

    /**
     * Backport of the pipe method from 5.2.
     *
     * @return void
     */
    private function pipe()
    {
        Collection::macro('pipe', function (callable $callback) {
            return $callback($this);
        });
    }

    /**
     * "Transpose" a multidimensional array.
     *
     * Rotate a multidimensional array, turning the rows into columns and columns into rows.
     * For example:
     *
     * $before = [
     *   [1, 2, 3],
     *   [4, 5, 6],
     *   [7, 8, 9],
     * ];
     *
     * $after = [
     *   [1, 4, 7],
     *   [2, 5, 8],
     *   [3, 6, 9],
     * ];
     *
     * @see https://adamwathan.me/2016/04/06/cleaning-up-form-input-with-transpose
     *
     * @return void
     */
    private function transpose()
    {
        // Simpler way, but only works on PHP 5.6
        // Collection::macro('transpose', function () {
        //     $items = array_map(function (...$items) {
        //         return $items;
        //     }, ...$this->values());

        //     return new static($items);
        // });

        Collection::macro('transpose', function () {
            $transposed = [];
            foreach ($this->values() as $value) {
                foreach ($value as $k => $v) {
                    $transposed[$k][] = $v;
                }
            }

            return new static($transposed);
        });
    }

    protected function toAugmentedArray()
    {
        Collection::macro('toAugmentedArray', function ($keys = null) {
            return array_map(function ($value) use ($keys) {
                if ($value instanceof Augmentable) {
                    return $value->toAugmentedArray($keys);
                }

                return $value instanceof Arrayable ? $value->toArray() : $value;
            }, $this->items);
        });

        Collection::macro('toAugmentedCollection', function ($keys = null) {
            return array_map(function ($value) use ($keys) {
                if ($value instanceof Augmentable) {
                    return $value->toAugmentedCollection($keys);
                }

                return $value instanceof Arrayable ? $value->toArray() : $value;
            }, $this->items);
        });

        Collection::macro('toEvaluatedAugmentedArray', function ($keys = null) {
            return array_map(function ($value) use ($keys) {
                if ($value instanceof Augmentable) {
                    return $value->toAugmentedCollection($keys)->withEvaluation()->toArray();
                }

                return $value instanceof Arrayable ? $value->toArray() : $value;
            }, $this->items);
        });
    }
}
