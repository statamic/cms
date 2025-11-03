<?php

namespace Statamic\Revisions;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Revisions\Revision as Contract;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Events\RevisionDeleted;
use Statamic\Events\RevisionSaved;
use Statamic\Events\RevisionSaving;
use Statamic\Facades;
use Statamic\Facades\Revision as Revisions;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Revision implements Arrayable, ContainsQueryableValues, Contract
{
    use ExistsAsFile, FluentlyGetsAndSets, TracksQueriedColumns, TracksQueriedRelations;

    protected $key;
    protected $date;
    protected $user;
    protected $userId;
    protected $message;
    protected $action = 'revision';
    protected $attributes = [];

    public function id()
    {
        return $this->key.'/'.($this->action === 'working' ? 'working' : $this->date()->timestamp);
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
        return $this->fluentlyGetOrSet('action')->args(func_get_args());
    }

    public function message($message = null)
    {
        return $this->fluentlyGetOrSet('message')->args(func_get_args());
    }

    public function attributes($attributes = null)
    {
        return $this->fluentlyGetOrSet('attributes')->args(func_get_args());
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
        return $this->fluentlyGetOrSet('key')->args(func_get_args());
    }

    public function date($date = null)
    {
        return $this->fluentlyGetOrSet('date')->args(func_get_args());
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.yaml', [
            Revisions::directory(),
            $this->key(),
            $this->action === 'working' ? 'working' : $this->date()->timestamp,
        ]);
    }

    public function fileData()
    {
        return [
            'action' => $this->action,
            'date' => $this->date->timestamp,
            'user' => $this->userId ?: null,
            'message' => $this->message ?: null,
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
            'id' => $this->id(),
            'action' => $this->action,
            'date' => $this->date()->timestamp,
            'user' => $user,
            'message' => $this->message,
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

    public function toWorkingCopy()
    {
        return (new self)
            ->action('working')
            ->key($this->key())
            ->date($this->date())
            ->user($this->user() ?? false)
            ->message($this->message() ?? false)
            ->attributes($this->attributes());
    }

    public function getQueryableValue(string $field)
    {
        return match ($field) {
            'id' => $this->id(),
            'key' => $this->key(),
            'action' => $this->action(),
            'date' => $this->date(),
            'user' => $this->userId,
            'message' => $this->message(),
            default => throw new \Exception('Field ['.$field.'] is not queryable on Revision.'),
        };
    }
}
