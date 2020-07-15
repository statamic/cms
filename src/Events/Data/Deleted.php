<?php

namespace Statamic\Events\Data;

use Statamic\Contracts\Git\ProvidesCommitMessage;

abstract class Deleted extends DataEvent implements ProvidesCommitMessage
{
    //
}
