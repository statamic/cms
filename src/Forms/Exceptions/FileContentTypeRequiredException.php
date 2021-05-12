<?php

namespace Statamic\Forms\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use LogicException;
use Statamic\Statamic;

class FileContentTypeRequiredException extends LogicException implements ProvidesSolution
{
    public function __construct()
    {
        parent::__construct('Cannot upload files without encoding as multipart form data.');
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create('Forms with file uploads need to have their data be encoded as multipart.')
            ->setSolutionDescription(
                "There are asset fields defined on this form's blueprint. The form needs `enctype=\"multipart/form-data\"`.
                You can add `files=\"true\"` to your `form:create` tag.
            ")
            ->setDocumentationLinks(['Read the forms guide' => Statamic::docsUrl('forms')]);
    }
}
