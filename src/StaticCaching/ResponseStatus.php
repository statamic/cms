<?php

namespace Statamic\StaticCaching;

enum ResponseStatus
{
    case HIT;
    case MISS;
    case UNDEFINED;
}
