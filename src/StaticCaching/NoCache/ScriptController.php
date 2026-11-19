<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Http\Response;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\FileCacher;

class ScriptController
{
    public function nocache(): Response
    {
        return $this->response($this->cacher()->getNocacheJs());
    }

    public function csrf(): Response
    {
        return $this->response($this->cacher()->getCsrfTokenJs());
    }

    private function cacher(): FileCacher
    {
        $cacher = app(Cacher::class);

        abort_unless($cacher instanceof FileCacher, 404);

        return $cacher;
    }

    private function response(string $js): Response
    {
        return response($js)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600')
            ->setEtag(md5($js));
    }
}
