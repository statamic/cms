<?php

namespace Statamic\Http\Controllers;

use Facades\Statamic\Fields\FieldtypeRepository as Fieldtype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Statamic\Exceptions\ForbiddenHttpException;
use Statamic\Fields\Field;

class DictionaryFieldtypeController extends Controller
{
    public function __invoke(Request $request, string $dictionary)
    {
        $dictionary = $this->fieldtype($request)->dictionary();

        if (Gate::denies('access cp') && ! $dictionary->allowsPublicAccess()) {
            throw new ForbiddenHttpException;
        }

        $options = $dictionary->options($request->search);

        // Return an ordered list of key/value pairs rather than a value-keyed object.
        // When the values are integers, the browser would re-sort the object's keys ascending,
        // discarding the dictionary's own order.
        return [
            'data' => collect($options)
                ->map(fn ($label, $key) => ['key' => (string) $key, 'value' => $label])
                ->values()
                ->all(),
        ];
    }

    protected function fieldtype($request)
    {
        $config = $this->getConfig($request);

        return Fieldtype::find($config['type'])->setField(
            new Field('relationship', $config)
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
