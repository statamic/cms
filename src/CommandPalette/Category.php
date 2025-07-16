<?php

namespace Statamic\CommandPalette;

enum Category: string
{
    case Recent = 'Recent';
    case Actions = 'Actions';
    case History = 'History';
    case Navigation = 'Navigation';
    case Preferences = 'Preferences';
    case Search = 'Content Search';

    public static function order()
    {
        return collect(self::cases())->map->value->all();
    }
}
