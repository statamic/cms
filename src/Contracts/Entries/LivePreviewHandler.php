<?php

namespace Statamic\Contracts\Entries;

interface LivePreviewHandler
{
    public function toLivePreviewResponse($entry, $request, $extras);
}
