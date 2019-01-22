<?php

namespace Statamic\Addons\Markdown;

use Statamic\Addons\BundleFieldtype as Fieldtype;

class MarkdownFieldtype extends Fieldtype
{
    public function augment($value)
    {
        return markdown($value);
    }
}
