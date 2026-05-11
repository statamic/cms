<?php

namespace Statamic\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Forms\Submission as SubmissionContract;
use Statamic\Contracts\Forms\SubmissionQueryBuilder;
use Statamic\Contracts\Forms\SubmissionRepository;

/**
 * @method static Collection all()
 * @method static Collection whereForm(string $handle)
 * @method static Collection whereInForm(array $handles)
 * @method static SubmissionContract find($id)
 * @method static void save(SubmissionContract $submission)
 * @method static void delete(SubmissionContract $submission)
 * @method static SubmissionQueryBuilder query()
 * @method static SubmissionContract make()
 *
 * @see \Statamic\Contracts\Forms\SubmissionRepository
 * @link \Statamic\Forms\Submission
 */
class FormSubmission extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SubmissionRepository::class;
    }
}
