<?php

namespace Statamic\Data;

use Illuminate\Support\Carbon;
use Statamic\Facades\User;

trait TracksLastModified
{
    public function lastModified()
    {
        return $this->has('updated_at')
            ? Carbon::createFromTimestamp($this->get('updated_at'))
            : $this->fileLastModified();
    }

    public function lastModifiedBy()
    {
        return $this->has('updated_by')
            ? User::find($this->get('updated_by'))
            : null;
    }

    public function updateLastModified($user = null)
    {
        if (! config('statamic.system.track_last_update')) {
            return $this;
        }

        $user
            ? $this->set('updated_by', $user->id())
            : $this->remove('updated_by');

        return $this->set('updated_at', Carbon::now()->timestamp);
    }

    public function touch($user = null)
    {
        $this->updateLastModified($user)->save();
    }
}
