<?php

namespace Statamic\Addons\UserPassword;

use Statamic\Data\User;
use Statamic\Extend\Fieldtype;

class UserPasswordFieldtype extends Fieldtype
{
    public $complete = true;

    /**
     * @return User;
     */
    private function user()
    {
        return $this->field_data;
    }

    /**
     * Is the password encrypted?
     *
     * @return bool
     */
    private function isEncrypted()
    {
        if ($this->user() instanceof User) {
            return $this->user()->isEncrypted();
        }

        return false;
    }

    /**
     * The field's html contents
     *
     * @return string
     */
    public function html()
    {
        return ($this->isEncrypted()) ? $this->encryptedField() : $this->regularField();
    }

    /**
     * The field when no password exists
     *
     * @return string
     */
    private function regularField()
    {
        return '
            <div class="form-group">
                <label>Password</label>
                <input type="password"
                    name="fields[password]"
                    class="form-control"
                    value="" />
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password"
                    name="fields[password_confirm]"
                    class="form-control"
                    value="" />
            </div>
        ';
    }

    /**
     * The field when a password exists
     *
     * @return string
     */
    private function encryptedField()
    {
        return 'Password is encrypted. <a href="" class="btn btn-xs btn-primary">Change</a>';
    }

    /**
     * Validation rules
     *
     * @return string
     */
    public function rules()
    {
        return 'same:fields.password_confirm';
    }
}
