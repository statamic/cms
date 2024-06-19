<?php

namespace Statamic\StaticCaching;

enum ResponseStatus: string
{
    case Hit = 'hit';
    case Miss = 'miss';
}
