<?php

namespace Statamic\Forms;

use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Facades;
use Statamic\Fields\Blueprint;
use Statamic\Statamic;

use function Statamic\trans as __;

class ConfigFields
{
    public static function blueprint(FormContract $form): Blueprint
    {
        $fields = static::fields();

        foreach (Facades\Form::extraConfigFor($form->handle()) as $handle => $config) {
            $merged = false;

            foreach ($fields as $sectionHandle => $section) {
                if ($section['display'] == __($config['display'])) {
                    $fields[$sectionHandle]['fields'] += $config['fields'];
                    $merged = true;
                }
            }

            if (! $merged) {
                $fields[$handle] = $config;
            }
        }

        return Facades\Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => collect($fields)->map(function ($section) {
                        return [
                            'display' => $section['display'],
                            'fields' => collect($section['fields'])->map(function ($field, $handle) {
                                return [
                                    'handle' => $handle,
                                    'field' => $field,
                                ];
                            })->values()->all(),
                        ];
                    })->values()->all(),
                ],
            ],
        ]);
    }

    public static function fields(): array
    {
        return [
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'validate' => 'required',
                        'instructions' => __('statamic::messages.form_configure_title_instructions'),
                    ],
                ],
            ],
            'fields' => [
                'display' => __('Fields'),
                'fields' => [
                    'honeypot' => [
                        'type' => 'text',
                        'instructions' => __('statamic::messages.form_configure_honeypot_instructions'),
                    ],
                ],
            ],
            'submissions' => [
                'display' => __('Submissions'),
                'fields' => [
                    'store' => [
                        'display' => __('Store Submissions'),
                        'type' => 'toggle',
                        'instructions' => __('statamic::messages.form_configure_store_instructions'),
                    ],
                    ...(Statamic::formsProInstalled() ? [
                        'unique_instances' => [
                            'display' => __('Unique Instances'),
                            'type' => 'toggle',
                            'instructions' => __('statamic::messages.form_configure_unique_instances_instructions'),
                        ],
                    ] : []),
                    'generate_fake_submissions' => [
                        'display' => __('Enable Fake Submission Generator'),
                        'type' => 'toggle',
                        'default' => true,
                        'instructions' => __('statamic::messages.form_configure_generate_fake_submissions_instructions'),
                    ],
                ],
            ],
            'access' => [
                'display' => __('Access'),
                'fields' => [
                    'close_date' => [
                        'display' => __('Close Date'),
                        'type' => 'date',
                        'time_enabled' => true,
                        'instructions' => __('statamic::messages.form_configure_close_date_instructions'),
                    ],
                    'submission_limit' => [
                        'display' => __('Submission Limit'),
                        'type' => 'integer',
                        'instructions' => __('statamic::messages.form_configure_submission_limit_instructions'),
                    ],
                    'submission_limit_period' => [
                        'display' => __('Submission Limit Period'),
                        'type' => 'button_group',
                        'default' => 'total',
                        'options' => [
                            'total' => __('Total'),
                            'day' => __('Per Day'),
                            'week' => __('Per Week'),
                            'month' => __('Per Month'),
                        ],
                        'if' => [
                            'submission_limit' => 'not empty',
                        ],
                        'instructions' => __('statamic::messages.form_configure_submission_limit_period_instructions'),
                    ],
                    'closed_message' => [
                        'display' => __('Closed Message'),
                        'type' => 'textarea',
                        'if_any' => [
                            'close_date' => 'not empty',
                            'submission_limit' => 'not empty',
                        ],
                        'placeholder' => __('statamic::messages.form_closed_message'),
                        'instructions' => __('statamic::messages.form_configure_closed_message_instructions'),
                    ],
                    'require_login' => [
                        'display' => __('Require Login'),
                        'type' => 'toggle',
                        'instructions' => __('statamic::messages.form_configure_require_login_instructions'),
                    ],
                    'require_login_message' => [
                        'display' => __('Require Login Message'),
                        'type' => 'textarea',
                        'if' => [
                            'require_login' => 'equals true',
                        ],
                        'placeholder' => __('statamic::messages.form_require_login_message'),
                        'instructions' => __('statamic::messages.form_configure_require_login_message_instructions'),
                    ],
                ],
            ],
            'email' => [
                'display' => __('Email'),
                'fields' => [
                    'email' => [
                        'type' => 'grid',
                        'mode' => 'stacked',
                        'full_width_setting' => true,
                        'add_row' => __('Add Email'),
                        'instructions' => __('statamic::messages.form_configure_email_instructions'),
                        'fields' => [
                            [
                                'handle' => 'to',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Recipient(s)'),
                                    'validate' => [
                                        'required',
                                    ],
                                    'instructions' => __('statamic::messages.form_configure_email_to_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'cc',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('CC Recipient(s)'),
                                    'instructions' => __('statamic::messages.form_configure_email_cc_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'bcc',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('BCC Recipient(s)'),
                                    'instructions' => __('statamic::messages.form_configure_email_bcc_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'from',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Sender'),
                                    'instructions' => __('statamic::messages.form_configure_email_from_instructions').' ('.config('mail.from.address').').',
                                ],
                            ],
                            [
                                'handle' => 'reply_to',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Reply To'),
                                    'instructions' => __('statamic::messages.form_configure_email_reply_to_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'subject',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Subject'),
                                    'instructions' => __('statamic::messages.form_configure_email_subject_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'html',
                                'field' => [
                                    'type' => 'template',
                                    'display' => __('HTML view'),
                                    'instructions' => __('statamic::messages.form_configure_email_html_instructions'),
                                    'folder' => config('statamic.forms.email_view_folder'),
                                    'clearable' => true,
                                ],
                            ],
                            [
                                'handle' => 'text',
                                'field' => [
                                    'type' => 'template',
                                    'display' => __('Text view'),
                                    'instructions' => __('statamic::messages.form_configure_email_text_instructions'),
                                    'folder' => config('statamic.forms.email_view_folder'),
                                    'clearable' => true,
                                ],
                            ],
                            [
                                'handle' => 'markdown',
                                'field' => [
                                    'type' => 'toggle',
                                    'display' => __('Markdown'),
                                    'instructions' => __('statamic::messages.form_configure_email_markdown_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'attachments',
                                'field' => [
                                    'type' => 'toggle',
                                    'display' => __('Attachments'),
                                    'instructions' => __('statamic::messages.form_configure_email_attachments_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'mailer',
                                'field' => [
                                    'type' => 'select',
                                    'instructions' => __('statamic::messages.form_configure_mailer_instructions'),
                                    'options' => array_keys(config('mail.mailers')),
                                    'clearable' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
