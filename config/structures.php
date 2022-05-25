<?php

return [
    /**
     * The Structure cache allows Nav and Collection Trees to be cached across page requests, speeding up secondary
     * requests for the Tree, which is really useful when you use {{ nav }} tags across multiple pages.
     *
     * You can completely disable this cache by setting the Time To Live value to `0`.
     *
     * Default: 1 week (7*24*60*60 = 604_800 seconds)
     */
    'cache_ttl' => env('STATAMIC_STRUCTURE_CACHE', 604_800),
];
