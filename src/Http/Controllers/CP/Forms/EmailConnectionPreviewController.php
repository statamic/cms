<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Forms\Connectors\Email as EmailConnector;
use Statamic\Forms\Email;
use Statamic\Forms\FakeSubmissionGenerator;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class EmailConnectionPreviewController extends CpController
{
    public function __invoke(Request $request, $form, EmailConnector $connector, FakeSubmissionGenerator $generator)
    {
        $connector->setForm($form);

        Validator::make([$request->all()], $connector->rules())->validate();

        $config = $connector->process([$request->all()])[0];
        $latest = $this->latestSubmission($form);
        $submission = $latest ?? $this->sampleSubmission($form, $generator);
        $email = new Email($submission, $config, $submission->site());

        try {
            $body = $email->render();
        } catch (\Throwable $e) {
            return response(['message' => $e->getMessage()], 422);
        }

        return [
            'subject' => $email->subject,
            'from' => $this->addresses($email->from ?: [config('mail.from')]),
            'to' => $this->addresses($email->to),
            'cc' => $this->addresses($email->cc),
            'bcc' => $this->addresses($email->bcc),
            'reply_to' => $this->addresses($email->replyTo),
            'format' => $email->view || $email->markdown ? 'html' : 'text',
            'body' => $body,
            'attachments' => Arr::get($config, 'attachments', false) && $this->hasUploadFields($form),
            'sample' => $latest === null,
        ];
    }

    private function hasUploadFields(Form $form): bool
    {
        return $form->blueprint()->fields()->all()
            ->contains(fn (Field $field) => in_array($field->type(), ['assets', 'files', 'form_upload']));
    }

    private function latestSubmission(Form $form): ?Submission
    {
        if (! User::current()->can('viewSubmissions', $form)) {
            return null;
        }

        return $form->querySubmissions()->whereNull('partial')->orderBy('date', 'desc')->first();
    }

    private function sampleSubmission(Form $form, FakeSubmissionGenerator $generator): Submission
    {
        $submission = $form->makeSubmission()->site(Site::selected());
        $submission->data($form->blueprint()->fields()->addValues($generator->generate($form))->process()->values());

        return $submission;
    }

    private function addresses(array $addresses): array
    {
        return collect($addresses)
            ->filter(fn (array $address) => filled($address['address']))
            ->map(fn (array $address) => filled($address['name'])
                ? "{$address['name']} <{$address['address']}>"
                : $address['address'])
            ->values()
            ->all();
    }
}
