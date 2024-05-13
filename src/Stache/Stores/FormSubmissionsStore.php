<?php

namespace Statamic\Stache\Stores;

use Illuminate\Support\Facades\Log;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Stache\Indexes\Value;
use Statamic\Support\Str;
use Statamic\Yaml\ParseException;
use Symfony\Component\Finder\SplFileInfo;

class FormSubmissionsStore extends ChildStore
{
    protected $valueIndex = Value::class;
    protected $storeIndexes = [
        'form',
        'date',
    ];

    public function getItemKey($item)
    {
        return $item->id();
    }

    public function getItemFilter(SplFileInfo $file)
    {
        $dir = Str::finish($this->directory(), '/');
        $relative = Str::after(Path::tidy($file->getPathname()), $dir);

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);

        try {
            $data = YAML::file($path)->parse($contents);
        } catch (ParseException $e) {
            $data = [];
            Log::warning('Could not parse form submission file: '.$path);
        }

        $form = pathinfo($path, PATHINFO_DIRNAME);
        $form = Str::after($form, $this->parent->directory());

        $form = Form::find($form);

        $submission = FormSubmission::make()
            ->id($handle)
            ->form($form)
            ->data($data);

        return $submission;
    }
}
