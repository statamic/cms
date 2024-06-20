<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Facades\Dictionary;
use Statamic\Http\Controllers\CP\CpController;

class DictionaryFieldtypeController extends CpController
{
    public function __invoke(Request $request, string $dictionary)
    {
        $dictionary = Dictionary::find($dictionary);

        return [
            'data' => $dictionary->options($request->search),
        ];
    }
}
