<?php

namespace Statamic\Enums;

enum VideoType: string
{
    case CloudflareStream = 'cloudflare_stream';
    case Custom = 'custom';
    case Vimeo = 'vimeo';
    case YouTube = 'youtube';
}
