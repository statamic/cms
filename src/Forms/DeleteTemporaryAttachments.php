<?php

namespace Statamic\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Forms\Submission;
use Statamic\Fields\Field;
use Statamic\Support\Arr;

class DeleteTemporaryAttachments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Submission $submission)
    {
    }

    public function handle()
    {
        $this->submission->form()->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->type() === 'files')
            ->each(function (Field $field) {
                $files = collect(Arr::wrap($this->submission->get($field->handle(), [])));

                $files->each(function ($path) {
                    Storage::disk('local')->delete('statamic/file-uploads/'.$path);
                });

                $this->submission->set($field->handle(), null)->saveQuietly();
            });
    }
}
