<?php

namespace Statamic\Forms\Connections;

use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use LogicException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Forms\DeleteTemporaryFiles;

class RecordConnectionSuccess
{
    const TTL = 60 * 60 * 24 * 8;

    public function __construct(public Submission $submission, public string $token)
    {
    }

    public static function ensureAttachable(object $job): void
    {
        // Queued jobs run through the middleware in their public $middleware property, which is where the countdown hooks in.
        if (! $job instanceof ShouldQueue || ! array_key_exists('middleware', get_object_vars($job))) {
            throw new LogicException('Form connection job ['.get_class($job).'] must implement '.ShouldQueue::class.' and have a public $middleware property, e.g. by using the '.Queueable::class.' trait.');
        }
    }

    public static function countDown(Submission $submission, array $jobs): void
    {
        rescue(fn () => static::cache()->put(static::key($submission), count($jobs), static::TTL));

        foreach ($jobs as $job) {
            $job->middleware[] = new self($submission, (string) Str::uuid());
        }
    }

    public function handle(object $job, Closure $next)
    {
        $result = $next($job);

        $queueJob = $job->job ?? null;

        if (! $queueJob?->isReleased() && ! $queueJob?->hasFailed()) {
            rescue(fn () => $this->recordSuccess());
        }

        return $result;
    }

    private function recordSuccess(): void
    {
        $cache = static::cache();
        $key = static::key($this->submission);

        // Guard against a job that runs twice (e.g. redelivery) counting down twice.
        if (! $cache->add("{$key}.{$this->token}", true, static::TTL)) {
            return;
        }

        // Decrementing a missing key would create a -1 that never expires.
        if (! $cache->has($key)) {
            return;
        }

        if ($cache->decrement($key) === 0) {
            Bus::dispatch(new DeleteTemporaryFiles($this->submission));
        }
    }

    private static function key(Submission $submission): string
    {
        return "statamic.form-connections.{$submission->form()->handle()}.{$submission->id()}";
    }

    private static function cache()
    {
        return Cache::store(config('statamic.forms.connections_cache_store'));
    }
}
