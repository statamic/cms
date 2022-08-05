<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Stache\Indexes;
use Statamic\Stache\Indexes\Value;
use Symfony\Component\Finder\SplFileInfo;

class FormSubmissionStore extends ChildStore
{
    protected $valueIndex = Value::class;
    protected $storeIndexes = [
        'form',
    ];

    public function getItemKey($item)
    {
        return $item->id();
    }

    public function getItemFilter(SplFileInfo $file)
    {
        $dir = str_finish($this->directory(), '/');
        $relative = str_after(Path::tidy($file->getPathname()), $dir);

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::file($path)->parse($contents);

        $form = pathinfo($path, PATHINFO_DIRNAME);
        $form = str_after($form, $this->parent->directory());

        $form = Form::find($form);

        $submission = FormSubmission::make()
            ->id($handle)
            ->form($form)
            ->data($data);

        return $submission;
    }
}
