<?php

namespace Statamic\CommandPalette;

enum Category: string
{
    case Actions = 'Actions';
    case History = 'History';
    case Navigation = 'Navigation';
    case Preferences = 'Preferences';
    case Search = 'Content Search';
}
