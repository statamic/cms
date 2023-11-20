<?php

namespace Statamic\Validation;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;

class AllowedFile implements InvokableRule
{
    private array $extensions = [
        '7z',
        'aiff',
        'asc',
        'asf',
        'avi',
        'avif',
        'bmp',
        'cap',
        'cin',
        'csv',
        'dfxp',
        'doc',
        'docx',
        'dotm',
        'dotx',
        'fla',
        'flv',
        'gif',
        'gz',
        'gzip',
        'heic',
        'heif',
        'hevc',
        'itt',
        'jp2',
        'jpeg',
        'jpg',
        'jpx',
        'js',
        'json',
        'lrc',
        'm2t',
        'm4a',
        'm4v',
        'mcc',
        'md',
        'mid',
        'mov',
        'mp3',
        'mp4',
        'mpc',
        'mpeg',
        'mpg',
        'mpsub',
        'ods',
        'odt',
        'ogg',
        'ogv',
        'pdf',
        'png',
        'potx',
        'pps',
        'ppsm',
        'ppsx',
        'ppt',
        'pptm',
        'pptx',
        'ppz',
        'pxd',
        'qt',
        'ram',
        'rar',
        'rm',
        'rmi',
        'rmvb',
        'rt',
        'rtf',
        'sami',
        'sbv',
        'scc',
        'sdc',
        'sitd',
        'smi',
        'srt',
        'stl',
        'sub',
        'svg',
        'swf',
        'sxc',
        'sxw',
        'tar',
        'tds',
        'tgz',
        'tif',
        'tiff',
        'ttml',
        'txt',
        'vob',
        'vsd',
        'vtt',
        'wav',
        'webm',
        'webp',
        'wma',
        'wmv',
        'xls',
        'xlsx',
        'zip',
    ];

    public function __invoke($attribute, $value, $fail): void
    {
        if (! $this->isAllowed($value)) {
            $fail(__('validation.uploaded'));
        }
    }

    private function isAllowed(UploadedFile $file): bool
    {
        $extensions = array_merge($this->extensions, config('statamic.assets.additional_uploadable_extensions', []));

        return in_array(trim(strtolower($file->getClientOriginalExtension())), $extensions);
    }
}
