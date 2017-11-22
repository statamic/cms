<?php

namespace Statamic\CP\Publish;

use Statamic\API\User;
use Statamic\API\Config;
use Statamic\API\Helper;

class UserPublisher extends Publisher
{
    protected $login_type;

    /**
     * Prepare the content object
     *
     * Retrieve, update, and/or create an Entry, depending on the situation.
     */
    protected function prepare()
    {
        $this->login_type = Config::get('users.login_type');

        $username = array_get($this->fields, 'username');
        $email = array_get($this->fields, 'email');

        $groups   = array_get($this->fields, 'user_groups', []);

        unset($this->fields['username'], $this->fields['user_groups'], $this->fields['status']);

        if ($this->isNew()) {
            // Creating a brand new user
            $user = User::create()->email($email);

            if ($this->login_type === 'username') {
                $user->username($username);
            }

            $this->content = $user->get();

            // Set the ID now because the $user->groups() method relies on it
            $this->id = Helper::makeUuid();
            $this->content->id($this->id);

            $this->addUserValidation('new');

        } else {
            // Updating an existing user
            $this->prepForExistingUser();

            $this->content->username($username);
            $this->content->email($email);

            $this->addUserValidation('existing');
        }

        $this->content->groups($groups);
    }

    /**
     * Prepare an existing user
     *
     * @throws \Exception
     */
    private function prepForExistingUser()
    {
        $this->id = $this->request->input('uuid');

        $this->content = User::find($this->id);
    }

    /**
     * Perform initial validation
     *
     * @throws \Statamic\Exceptions\PublishException
     */
    protected function initialValidation()
    {
        //
    }

    /**
     * Add validation rules to the fieldset.
     *
     * Since the fieldset is user-editable, we can't guarantee that they
     * would have added all the essential validation rules we require.
     *
     * @param string $type  Either "new" or "existing"
     */
    private function addUserValidation($type)
    {
        $fieldset = $this->content->fieldset();

        $fields = $fieldset->fields();

        $fields = ($type === 'new') ? $this->addNewUserValidation($fields) : $this->addExistingUserValidation($fields);

        $fields = $this->addBasicUserValidation($fields);

        $fieldset->fields($fields);

        $this->content->fieldset($fieldset);
    }

    /**
     * Add some basic validation to the fields array, and return it.
     *
     * @param array $fields
     * @return array
     */
    private function addBasicUserValidation($fields)
    {
        // Ensure the username field is required. We'll break it into an array and rejoin it so
        // we can avoid duplication if the fieldset already contained required validation.
        $username_rules = $this->appendRule('required', array_get($fields, 'username.validate'));
        array_set($fields, 'username.validate', $username_rules);

        // If the login type is email, we'll change the "username" field to "email".
        if ($this->login_type === 'email') {
            $fields['email'] = array_merge($fields['email'], $fields['username']);
            unset($fields['username']);
        }

        // If there's an email field, make sure it is validated as one.
        if (isset($fields['email'])) {
            $email_rules = $this->appendRule('email', array_get($fields, 'email.validate'));
            array_set($fields, 'email.validate', $email_rules);
        }

        return $fields;
    }

    /**
     * Add validation for creating new users, and return the fields
     *
     * @param array $fields
     * @return array
     */
    private function addNewUserValidation($fields)
    {
        $rules = array_get($fields, 'username.validate');

        $usernames = User::all()->map(function ($user) {
            return $user->username();
        });

        $rules = ltrim($rules . '|not_in:' . $usernames->implode(','), '|');

        array_set($fields, 'username.validate', $rules);

        return $fields;
    }

    /**
     * Add validation for updating existing users, and return the fields
     *
     * @param array $fields
     * @return array
     */
    private function addExistingUserValidation($fields)
    {
        $rules = array_get($fields, 'username.validate');

        // Get all the usernames, except for the user being edited.
        // Obviously it's okay for the user being edited to have the same username.
        $usernames = User::all()->map(function ($user) {
            return $user->username();
        })->reject(function ($username) {
            return $username === $this->content->username();
        });

        // Only apply the rule if there are usernames. If there is only one
        // user in the system, then the array will be empty at this point.
        if (! $usernames->isEmpty()) {
            $rules = ltrim($rules . '|not_in:' . $usernames->implode(','), '|');
        }

        array_set($fields, 'username.validate', $rules);

        return $fields;
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
