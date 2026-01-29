<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Forms\FormRepository;
use Statamic\Contracts\Forms\Submission;
use Statamic\Forms\Exporters\ExporterRepository;

/**
 * @method static \Statamic\Contracts\Forms\Form find(string $handle)
 * @method static \Statamic\Contracts\Forms\Form findOrFail(string $handle)
 * @method static \Illuminate\Support\Collection all()
 * @method static int count()
 * @method static \Statamic\Contracts\Forms\Form make(string $handle = null)
 * @method static void appendConfigFields(mixed $handles, string $display, array $fields)
 * @method static array extraConfigFor(string $handle)
 * @method static self redirect(string $form, \Closure $callback)
 * @method static \Closure getSubmissionRedirect(Submission $submission)
 * @method static ExporterRepository exporters()
 *
 * @see \Statamic\Contracts\Forms\FormRepository
 * @link \Statamic\Forms\Form
 */
class Form extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FormRepository::class;
    }
}
