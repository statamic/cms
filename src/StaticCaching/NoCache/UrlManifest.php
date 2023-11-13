<?php

namespace Statamic\StaticCaching\NoCache;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class UrlManifest
{
    public function add(string $url)
    {
        if ($this->exists($url)) {
            return;
        }

        $batch = Carbon::now()->format('Ymd');

        $this->cacheUrls($batch, $this->urls($batch)->push($url)->all());

        $this->addToBatchManifest($batch);
    }

    private function exists(string $url): bool
    {
        return collect($this->batches())
            ->contains(fn ($batch) => collect(Cache::get('nocache::urls.'.$batch, []))->contains($url));
    }

    private function addToBatchManifest(string $batch)
    {
        Cache::forever('nocache::batches', $this->batches()->push($batch)->unique()->all());
    }

    private function batches(): Collection
    {
        return collect(Cache::get('nocache::batches', []));
    }

    private function urls(string $batch): Collection
    {
        return collect(Cache::get('nocache::urls.'.$batch, []));
    }

    private function cacheUrls(string $batch, array $urls)
    {
        Cache::forever('nocache::urls.'.$batch, $urls);
    }

    public function flush()
    {
        $this->batches()->each(fn ($batch) => Cache::forget('nocache::urls.'.$batch));

        Cache::forget('nocache::batches');
    }

    public function all(): Collection
    {
        return $this->batches()->flatMap(fn (string $batch) => $this->urls($batch));
    }
}
