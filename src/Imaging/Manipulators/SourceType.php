<?php

namespace Statamic\Imaging\Manipulators;

enum SourceType
{
    case Asset;
    case AssetId;
    case Url;
    case Path;
}
