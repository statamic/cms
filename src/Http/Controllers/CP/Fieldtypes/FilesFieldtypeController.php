<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository as Fieldtype;
use Illuminate\Http\Request;
use Statamic\Assets\FileUploader as Uploader;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Rules\AllowedFile;

class FilesFieldtypeController extends CpController
{
    public function upload(Request $request)
    {
        $fieldtype = $request->config ? $this->fieldtype($request) : null;

        $request->validate([
            'file' => ['file', new AllowedFile($fieldtype?->config('allowed_extensions'))],
        ]);

        $file = $request->file('file');

        $path = Uploader::container($request->container)->upload($file);

        return ['data' => ['id' => $path]];
    }

    protected function fieldtype($request)
    {
        $config = $this->getConfig($request);

        return Fieldtype::find($config['type'])->setField(
            new Field('file', $config)
        );
    }

    private function getConfig($request)
    {
        // The fieldtype base64-encodes the config.
        $json = base64_decode($request->config);

        // The json may include unicode characters, so we'll try to convert it to UTF-8.
        // See https://github.com/statamic/cms/issues/566
        $utf8 = mb_convert_encoding($json, 'UTF-8', mb_list_encodings());

        // In PHP 8.1 there's a bug where encoding will return null. It's fixed in 8.1.2.
        // In this case, we'll fall back to the original JSON, but without the encoding.
        // Issue #566 may still occur, but it's better than failing completely.
        $json = empty($utf8) ? $json : $utf8;

        return json_decode($json, true);
    }
}
