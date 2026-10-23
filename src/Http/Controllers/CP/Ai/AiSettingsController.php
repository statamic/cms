<?php

namespace Statamic\Http\Controllers\CP\Ai;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\CP\PublishForm;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

/**
 * Prototype: Settings → AI, where AI gets set up for the whole site.
 *
 * It's a mock for the design. The form is real (a blueprint shown with PublishForm, like an addon's settings), but the values
 * are only kept in the cache, and nothing reads them yet. A real version would save them somewhere proper (the API key in .env),
 * and the AI chat and field suggestions would use them.
 *
 * Who can use AI isn't set here. That's the "Use AI" permission on each role.
 */
class AiSettingsController extends CpController
{
    private const CACHE_KEY = 'statamic.prototype.ai-settings';

    public function edit()
    {
        $this->onlySuperUsers();

        return PublishForm::make($this->blueprint())
            ->asConfig()
            ->icon('ai-chat')
            ->title(__('AI'))
            ->values(array_merge($this->defaults(), Cache::get(self::CACHE_KEY, [])))
            ->submittingTo(cp_route('ai.update'));
    }

    public function update(Request $request)
    {
        $this->onlySuperUsers();

        $values = PublishForm::make($this->blueprint())->submit($request->all());

        Cache::forever(self::CACHE_KEY, $values);

        return ['saved' => true];
    }

    // Only super users, until there's a permission for it
    private function onlySuperUsers(): void
    {
        abort_unless(User::current()->isSuper(), 403);
    }

    private function defaults(): array
    {
        return [
            'provider' => 'anthropic',
            'assistant' => true,
            'suggestions' => true,
            // Common handles for personal details, erring on the side of privacy
            'excluded_fields' => [
                // Names
                'first_name', 'last_name', 'surname', 'full_name',
                // Contact
                'email', 'email_address', 'phone', 'phone_number', 'telephone', 'tel', 'mobile', 'mobile_number',
                // Address
                'address', 'address_line_1', 'address_line_2', 'street', 'postcode', 'postal_code', 'zip', 'zip_code',
                // Birth date
                'date_of_birth', 'dob', 'birthday',
                // Identity and money
                'ssn', 'national_insurance', 'tax_id', 'passport_number', 'iban', 'account_number', 'sort_code', 'card_number',
                // Tracking
                'ip', 'ip_address',
            ],
            'excluded_other' => ['users', 'form_submissions'],
        ];
    }

    private function blueprint(): Blueprint
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => __('Provider'),
                            'instructions' => __('Which AI service runs the AI chat and writes suggestions. Your content is sent to it, so pick one you trust with it.'),
                            'fields' => [
                                [
                                    'handle' => 'provider',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('Provider'),
                                        'options' => [
                                            'anthropic' => 'Anthropic (Claude)',
                                            'openai' => 'OpenAI',
                                            'google' => 'Google (Gemini)',
                                            'mistral' => 'Mistral',
                                            'ollama' => __('Ollama (self-hosted)'),
                                        ],
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'model',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Model'),
                                        'instructions' => __("Leave empty for the provider's recommended model."),
                                        'placeholder' => 'claude-sonnet-5-5',
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'suggestions_model',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Model for Suggestions'),
                                        'instructions' => __('Suggestions run often and only need short answers, so a smaller, cheaper model usually does. Leave empty to use the model above.'),
                                        'placeholder' => 'claude-haiku-5-5',
                                        'if' => ['suggestions' => true],
                                    ],
                                ],
                                [
                                    'handle' => 'api_key',
                                    'field' => [
                                        'type' => 'text',
                                        'input_type' => 'password',
                                        'display' => __('API Key'),
                                        'instructions' => __('Kept in your .env file as STATAMIC_AI_KEY, never with your content.'),
                                        'unless' => ['provider' => 'ollama'],
                                    ],
                                ],
                                [
                                    'handle' => 'ollama_url',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Ollama URL'),
                                        'placeholder' => 'http://localhost:11434',
                                        'if' => ['provider' => 'ollama'],
                                    ],
                                ],
                                [
                                    'handle' => 'connection',
                                    'field' => [
                                        'type' => 'ai_status',
                                        'show' => 'connection',
                                        'display' => __('Connection'),
                                        'instructions' => __('Check the details above work before anyone relies on them.'),
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Features'),
                            // Bold, as it's the one thing about AI that isn't set on this page
                            'instructions' => __('Who can use them is set per role, with the **Use AI** permission.'),
                            'fields' => [
                                [
                                    'handle' => 'assistant',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('AI Chat'),
                                        'instructions' => __('Opened from the *Ask* button in the header.'),
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'suggestions',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('Suggestions on fields'),
                                        'instructions' => __('Switched on for each field in its blueprint.'),
                                        'width' => 50,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Writing Style'),
                            'instructions' => __('Given to the AI with every request, so what it writes sounds like the rest of the site.'),
                            'fields' => [
                                [
                                    'handle' => 'instructions',
                                    'field' => [
                                        'type' => 'textarea',
                                        'display' => __('Instructions'),
                                        'instructions' => __('Tone, spelling, and words to use or avoid.'),
                                        'placeholder' => __('British English. Friendly but not chatty. Say "customers", never "users".'),
                                    ],
                                ],
                                [
                                    'handle' => 'collection_instructions',
                                    'field' => [
                                        'type' => 'grid',
                                        'display' => __('Per Collection'),
                                        'instructions' => __('Extra instructions for entries in a particular collection, on top of the ones above.'),
                                        'add_row' => __('Add Collection'),
                                        'fullscreen' => false,
                                        'fields' => [
                                            [
                                                'handle' => 'collection',
                                                'field' => [
                                                    'type' => 'collections',
                                                    'display' => __('Collection'),
                                                    'max_items' => 1,
                                                    'mode' => 'select',
                                                ],
                                            ],
                                            [
                                                'handle' => 'instructions',
                                                'field' => [
                                                    'type' => 'textarea',
                                                    'display' => __('Instructions'),
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                // Only on multisite, e.g. British English on the UK site and American English on the US one
                                ...(Site::multiEnabled() ? [[
                                    'handle' => 'site_instructions',
                                    'field' => [
                                        'type' => 'grid',
                                        'display' => __('Per Site'),
                                        'instructions' => __("Extra instructions for a particular site. The AI already writes in each site's language."),
                                        'add_row' => __('Add Site'),
                                        'fullscreen' => false,
                                        'fields' => [
                                            [
                                                'handle' => 'site',
                                                'field' => [
                                                    'type' => 'sites',
                                                    'display' => __('Site'),
                                                    'max_items' => 1,
                                                    'mode' => 'select',
                                                ],
                                            ],
                                            [
                                                'handle' => 'instructions',
                                                'field' => [
                                                    'type' => 'textarea',
                                                    'display' => __('Instructions'),
                                                ],
                                            ],
                                        ],
                                    ],
                                ]] : []),
                            ],
                        ],
                        [
                            'display' => __('Kept Out of AI'),
                            'instructions' => __('Anything you add here is never sent to the AI provider, even for users who can see it. Use this for personal details, like names and email addresses, and anything confidential.'),
                            'fields' => [
                                [
                                    'handle' => 'excluded_collections',
                                    'field' => [
                                        'type' => 'collections',
                                        'display' => __('Collections'),
                                        'mode' => 'select',
                                    ],
                                ],
                                [
                                    'handle' => 'excluded_taxonomies',
                                    'field' => [
                                        'type' => 'taxonomies',
                                        'display' => __('Taxonomies'),
                                        'mode' => 'select',
                                    ],
                                ],
                                [
                                    'handle' => 'excluded_fields',
                                    'field' => [
                                        'type' => 'taggable',
                                        'display' => __('Fields'),
                                        'instructions' => __('Field handles to leave out wherever they appear, like `email` or `phone`.'),
                                        'placeholder' => __('Type a handle and press Enter'),
                                    ],
                                ],
                                [
                                    'handle' => 'excluded_other',
                                    'field' => [
                                        'type' => 'checkboxes',
                                        'display' => __('Also Keep Out'),
                                        'options' => [
                                            'users' => __('Users'),
                                            'form_submissions' => __('Form submissions'),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => __('Spending'),
                            'fields' => [
                                [
                                    'handle' => 'usage',
                                    'field' => [
                                        'type' => 'ai_status',
                                        'show' => 'usage',
                                        'display' => __('This Month'),
                                    ],
                                ],
                                [
                                    'handle' => 'monthly_limit',
                                    'field' => [
                                        'type' => 'integer',
                                        'display' => __('Monthly Limit'),
                                        'instructions' => __('The AI chat and suggestions pause for the rest of the month once the provider has charged this much. Leave empty for no limit.'),
                                        'prepend' => '$',
                                        'width' => 50,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
