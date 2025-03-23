<?php

namespace Statamic\CommandPalette;

enum Category
{
    case Actions;
    case History;
    case Navigation;
    case Preferences;
    case Entries;
    case Taxonomies;
    case Globals;
    case Users;
}
