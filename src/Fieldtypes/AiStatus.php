<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

/**
 * Prototype: read-only bits of the Settings → AI page that aren't settings, like the "Test connection" button and this month's spending.
 * The `show` config picks which ("connection" or "usage"). Both are mocked in the Vue component, and nothing is saved.
 */
class AiStatus extends Fieldtype
{
    protected $selectable = false;
    protected $localizable = false;
    protected $validatable = false;
    protected $defaultable = false;
}
