<?php

namespace Statamic\Data\Eloquent;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Statamic\Contracts\Data\User as UserContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements UserContract, AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get a user's ID
     *
     * @return mixed
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    /**
     * Get a user's username
     *
     * @return mixed
     */
    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    /**
     * Get a user's email address
     *
     * @return mixed
     */
    public function getEmail()
    {
        // TODO: Implement getEmail() method.
    }

    /**
     * Get a user's password
     *
     * @return string
     */
    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    /**
     * Add supplemental data to the attributes
     */
    public function supplement()
    {
        // TODO: Implement supplement() method.
    }
}
