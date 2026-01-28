<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\StaticCache;
use Statamic\Statamic;

class StaticRecacheToken extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:static:recache-token {--raw : Output the raw token without formatting}';

    protected $description = 'Output the recache token';

    public function handle()
    {
        $token = StaticCache::recacheToken();

        if ($this->option('raw')) {
            $this->line($token);

            return self::SUCCESS;
        }

        $this->components->info('Your recache token is:');

        $this->line($token);
        $this->newLine();

        $this->components->bulletList([
            'Paste this token into your server\'s rewrite rules.',
            'For example your nginx config or .htaccess file.',
            'See '.Statamic::docsUrl('static-caching').' for more information.',
        ]);

        return self::SUCCESS;
    }
}
