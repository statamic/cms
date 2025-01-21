<?php

namespace Statamic\Stache\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Forms\Submission;
use Statamic\Contracts\Forms\SubmissionQueryBuilder;
use Statamic\Contracts\Forms\SubmissionRepository as RepositoryContract;
use Statamic\Stache\Stache;

class SubmissionRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('form-submissions');
    }

    public function all(): Collection
    {
        return $this->query()->get();
    }

    public function whereForm(string $handle): Collection
    {
        return $this->query()->where('form', $handle)->get();
    }

    public function whereInForm(array $handles): Collection
    {
        return $this->query()->whereIn('form', $handles)->get();
    }

    public function find($id): ?Submission
    {
        return $this->query()->where('id', $id)->first();
    }

    public function save($submission)
    {
        $this->store
            ->store($submission->form()->handle())
            ->save($submission);
    }

    public function delete($submission)
    {
        $this->store
            ->store($submission->form()->handle())
            ->delete($submission);
    }

    public function query()
    {
        return app(SubmissionQueryBuilder::class);
    }

    public function make(): Submission
    {
        return app(Submission::class);
    }

    public static function bindings(): array
    {
        return [
            Submission::class => \Statamic\Forms\Submission::class,
            SubmissionQueryBuilder::class => \Statamic\Stache\Query\SubmissionQueryBuilder::class,
        ];
    }
}
