<?php

namespace Statamic\Revisions;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Revisions\Revision as Contract;
use Statamic\Data\ExistsAsFile;
use Statamic\Events\RevisionDeleted;
use Statamic\Events\RevisionSaved;
use Statamic\Events\RevisionSaving;
use Statamic\Facades;
use Statamic\Facades\Revision as Revisions;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Revision implements Arrayable, Contract
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $id;
    protected $key;
    protected $date;
    protected $user;
    protected $userId;
    protected $message;
    protected $publishAt;
    protected $action = 'revision';
    protected $attributes = [];

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->value($id);
    }

    public function user($user = null)
    {
        if (is_null($user)) {
            if ($this->user) {
                return $this->user;
            }

            return $this->user = Facades\User::find($this->userId ?: null);
        }

        if ($user instanceof User) {
            $this->user = $user;
            $user = $user->id();
        }

        $this->userId = $user;

        return $this;
    }

    public function action($action = null)
    {
        return $this->fluentlyGetOrSet('action')->value($action);
    }

    public function message($message = null)
    {
        return $this->fluentlyGetOrSet('message')->value($message);
    }

    public function publishAt($dateTime = null)
    {
        return $this->fluentlyGetOrSet('publishAt')->value($dateTime);
    }

    public function attributes($attributes = null)
    {
        return $this->fluentlyGetOrSet('attributes')->value($attributes);
    }

    public function attribute(string $key, $value = null)
    {
        if (func_num_args() == 1) {
            return $this->attributes[$key];
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    public function key($key = null)
    {
        return $this->fluentlyGetOrSet('key')->value($key);
    }

    public function date($date = null)
    {
        return $this->fluentlyGetOrSet('date')->value($date);
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.yaml', [
            Revisions::directory(),
            $this->key(),
            $this->date()->timestamp,
        ]);
    }

    public function fileData()
    {
        return [
            'action' => $this->action,
            'date' => $this->date->timestamp,
            'user' => $this->userId ?: null,
            'message' => $this->message ?: null,
            'publish_at' => $this->publishAt?->timestamp,
            'attributes' => $this->attributes,
        ];
    }

    public function toArray()
    {
        if ($user = $this->user()) {
            $user = [
                'id' => $user->id(),
                'email' => $user->email(),
                'name' => $user->name(),
                'avatar' => $user->avatar(),
                'initials' => $user->initials(),
            ];
        }

        return [
            'id' => $this->id,
            'action' => $this->action,
            'date' => $this->date()->timestamp,
            'user' => $user,
            'message' => $this->message,
            'publish_at' => $this->publishAt,
            'attributes' => $this->attributes,
        ];
    }

    public function save()
    {
        if (RevisionSaving::dispatch($this) === false) {
            return false;
        }

        Revisions::save($this);

        RevisionSaved::dispatch($this);

        return true;
    }

    public function delete()
    {
        Revisions::delete($this);

        RevisionDeleted::dispatch($this);
    }
}
