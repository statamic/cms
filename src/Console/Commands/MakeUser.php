<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Rules\EmailAvailable;
use Statamic\Statamic;
use Symfony\Component\Console\Input\InputArgument;

class MakeUser extends Command
{
    use RunsInPlease, ValidatesInput;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * The user's email.
     *
     * @var string
     */
    protected $email;

    /**
     * The user's data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! Statamic::pro() && User::query()->count() > 0) {
            return $this->error(__('Statamic Pro is required.'));
        }

        // If email argument exists, non-interactively create user.
        if ($this->email = $this->argument('email')) {
            return $this->createUser();
        }

        // Otherwise, interactively prompt for data and create user..
        $this
            ->promptEmail()
            ->promptName()
            ->promptPassword()
            ->promptSuper()
            ->createUser();
    }

    /**
     * Prompt for an email address.
     *
     * @return $this
     */
    protected function promptEmail()
    {
        $this->email = $this->ask('Email');

        if ($this->emailValidationFails()) {
            return $this->promptEmail();
        }

        return $this;
    }

    /**
     * Prompt for a name.
     *
     * @return $this
     */
    protected function promptName()
    {
        if ($this->hasSeparateNameFields()) {
            return $this->promptSeparateNameFields();
        }

        $this->data['name'] = $this->ask('Name', false);

        return $this;
    }

    /**
     * Prompt for first name and last name separately.
     *
     * @return $this
     */
    protected function promptSeparateNameFields()
    {
        $this->data['first_name'] = $this->ask('First Name', false);
        $this->data['last_name'] = $this->ask('Last Name', false);

        return $this;
    }

    /**
     * Prompt for a password.
     *
     * @return $this
     */
    protected function promptPassword()
    {
        $this->data['password'] = $this->secret('Password (Your input will be hidden)');

        return $this;
    }

    /**
     * Prompt for super permissions.
     *
     * @return $this
     */
    protected function promptSuper()
    {
        $this->data['super'] = (bool) $this->confirm('Super user', false);

        return $this;
    }

    /**
     * Create the user.
     */
    protected function createUser()
    {
        // Also validate here for when creating non-interactively.
        if ($this->emailValidationFails()) {
            return;
        }

        User::make()
            ->email($this->email)
            ->data($this->data)
            ->save();

        $this->info('User created successfully.');
    }

    /**
     * Check if email validation fails.
     *
     * @return bool
     */
    protected function emailValidationFails()
    {
        return $this->validationFails($this->email, ['required', new EmailAvailable, 'email']);
    }

    /**
     * Check if the user fieldset contains separate first_name and last_name fields.
     * Note: Though this isn't true by default, it's a common modification, and/or
     * they may have chosen to keep these fields separte when migrating from v2.
     *
     * @return bool
     */
    protected function hasSeparateNameFields()
    {
        $fields = Blueprint::find('user')->fields()->all();

        return $fields->has('first_name') && $fields->has('last_name');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['email', InputArgument::OPTIONAL, 'Non-interactively create a user with only an email address'],
        ];
    }
}
