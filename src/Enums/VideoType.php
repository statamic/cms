<?php

namespace Statamic\Enums;

enum VideoType: string
{
    case CloudflareStream = 'cloudflare_stream';
    case Invalid = 'invalid';
    case Vimeo = 'vimeo';
    case YouTube = 'youtube';
}
