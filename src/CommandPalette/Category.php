<?php

namespace Statamic\CommandPalette;

enum Category: string
{
    case Actions = 'Actions';
    case Recent = 'Recent';
    case History = 'History';
    case Navigation = 'Navigation';
    case Fields = 'Fields';
    case Miscellaneous = 'Miscellaneous';
    case Preferences = 'Preferences';
    case Search = 'Content Search';

    public static function order()
    {
        return collect(self::cases())->map->value->all();
    }
}
