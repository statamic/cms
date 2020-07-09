<?php

namespace Statamic\Events\Data;

use Statamic\Contracts\Git\ProvidesCommitMessage;

abstract class Saved extends DataEvent implements ProvidesCommitMessage
{
    //
}
