<?php

namespace Statamic\Forms\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use LogicException;
use Statamic\Forms\Form;
use Statamic\Statamic;

class BlueprintUndefinedException extends LogicException implements ProvidesSolution
{
    protected $form;

    public static function create(Form $form)
    {
        return (new static("Form [{$form->handle()}] does not have a blueprint"))->setForm($form);
    }

    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create("The {$this->form->handle()} form does not have a blueprint defined.")
            ->setSolutionDescription("A blueprint defines the form's available fields and their behaviors.\n\nYou can add `blueprint: handle` to a form's YAML file.")
            ->setDocumentationLinks([
                'Read the forms guide' => Statamic::docsUrl('forms'),
            ]);
    }
}
