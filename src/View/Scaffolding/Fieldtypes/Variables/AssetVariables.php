<?php

namespace Statamic\View\Scaffolding\Fieldtypes\Variables;

class AssetVariables
{
    public static function baseVariables(): array
    {
        return [
            'id',
            'title',
            'path',
            'filename',
            'basename',
            'extension',
            'is_asset',
            'is_audio',
            'is_previewable',
            'is_image',
            'is_svg',
            'is_video',
            'blueprint',
            'edit_url',
            'container',
            'folder',
            'url',
            'permalink',
            'api_url',
        ];
    }

    public static function metadataVariables(): array
    {
        return [

            'size',
            'size_bytes',
            'size_kilobytes',
            'size_megabytes',
            'size_gigabytes',
            'size_b',
            'size_kb',
            'size_mb',
            'size_gb',
            'last_modified',
            'last_modified_timestamp',
            'last_modified_instance',
            'focus',
            'has_focus',
            'focus_css',
            'height',
            'width',
            'orientation',
            'ratio',
            'mime_type',
            'duration',
            'duration_seconds',
            'duration_minutes',
            'duration_sec',
            'duration_min',
            'playtime',
        ];
    }
}
