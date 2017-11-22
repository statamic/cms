<?php

namespace Statamic\Addons\User;

use Validator;
use Statamic\API\User;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Fieldset;
use Illuminate\Http\Request;
use Statamic\CP\Publish\ValidationBuilder;

class UserRegistrar
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Fields to be validated
     *
     * @var array
     */
    protected $fields;

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->fieldset = Fieldset::get('user');
    }

    /**
     * Get the Validator instance
     *
     * @return mixed
     */
    public function validator()
    {
        $builder = $this->validationBuilder();

        return Validator::make(['fields' => $this->fields], $builder->rules(), [], $builder->attributes());
    }

    /**
     * Create the user
     *
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function create()
    {
        $user = User::create()
            ->username($this->request->input(Config::get('users.login_type')))
            ->with($this->userData())
            ->get();

        return $user;
    }

    /**
     * @return \Statamic\CP\Publish\ValidationBuilder
     */
    protected function validationBuilder()
    {
        $this->adjustFieldset();

        // Remove any unwanted request input to be validated
        $this->fields = $this->request->except('redirect');

        // Build the validation rules/attributes based on the user fieldset
        $builder = new ValidationBuilder(['fields' => $this->fields], $this->fieldset);
        $builder->build();

        return $builder;
    }

    /**
     * Add some additional fields and validation rules to the fieldset
     *
     * @return void
     */
    protected function adjustFieldset()
    {
        $fields = $this->fieldset->fields();

        $username_rules = array_get($fields, 'username.validate');

        // Get all the usernames so we can prevent a duplicate from being used.
        $usernames = User::all()->map(function ($user) {
            return $user->username();
        });

        $username_rules = ltrim($username_rules . '|not_in:' . $usernames->implode(','), '|');

        // Ensure the username field is required. We'll break it into an array and rejoin it so
        // we can avoid duplication if the fieldset already contained required validation.
        $username_rules = $this->appendRule('required', $username_rules);
        array_set($fields, 'username.validate', $username_rules);

        // If the login type is email, we'll change the "username" field to "email".
        if (Config::get('users.login_type') === 'email') {
            $fields['email'] = array_merge($fields['email'], $fields['username']);
            unset($fields['username']);
        }

        // If there's an email field, make sure it is validated as one.
        if (isset($fields['email'])) {
            $email_rules = $this->appendRule('email', array_get($fields, 'email.validate'));
            array_set($fields, 'email.validate', $email_rules);
        }

        // Need to validate a password and make sure it's confirmed.
        array_set($fields, 'password', [
            'display' => trans_choice('cp.passwords', 1),
            'validate' => 'required|confirmed'
        ]);

        $this->fieldset->fields($fields);
    }

    /**
     * Get the data to be stored in the user
     *
     * @return mixed
     */
    protected function userData()
    {
        // We're mapping to null values and filtering here because
        // ->filter() doesn't pass along the keys in Laravel 5.1.
        $data = collect($this->fields)->map(function ($value, $key) {
            return (in_array($key, $this->whitelistedFields())) ? $value : null;
        })->filter()->all();

        if ($roles = Config::get('users.new_user_roles')) {
            $data['roles'] = Helper::ensureArray($roles);
        }

        return $data;
    }

    /**
     * Get the fields that shouldn't be added to a user
     *
     * @return array
     */
    protected function blacklistedFields()
    {
        return ['username', 'roles'];
    }

    /**
     * Get the fields that are allowed to be added to a user
     *
     * @return array
     */
    protected function whitelistedFields()
    {
        return array_diff(array_keys($this->fieldset->fields()), $this->blacklistedFields());
    }

    /**
     * Append a validation rule to other validation rules
     *
     * @param string $rule
     * @param string $rules
     * @return string
     */
    private function appendRule($rule, $rules)
    {
        $rules = explode('|', $rules);
        $rules[] = $rule;
        $rules = join('|', array_values(array_filter(array_unique($rules))));

        return $rules;
    }
}