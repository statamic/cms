<?php

namespace Statamic\Contracts\Entries;

interface LivePreview
{
    public function toLivePreviewResponse($entry, $request, $extras);
}
