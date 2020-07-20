<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

abstract class Deleted extends Event implements ProvidesCommitMessage
{
    //
}
