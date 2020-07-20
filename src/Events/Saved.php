<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

abstract class Saved extends DataEvent implements ProvidesCommitMessage
{
    //
}
