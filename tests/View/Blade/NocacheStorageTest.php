<?php

namespace Tests\View\Blade;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Blade\AntlersBladePrecompiler;
use Statamic\View\Blade\StatamicTagCompiler;
use Tests\TestCase;

class NocacheStorageTest extends TestCase
{
    #[Test]
    public function it_stores_compiled_nocache_components_in_statamic_storage()
    {
        $compiled = (new StatamicTagCompiler())->compile('<s:nocache>Hello</s:nocache>');
        $viewName = '_nocache'.sha1('Hello');

        $this->assertSame("@nocache('compiled__views::{$viewName}')", $compiled);
        $this->assertSame('Hello', file_get_contents(storage_path("statamic/tmp/nocache/{$viewName}.blade.php")));
    }

    #[Test]
    public function it_stores_precompiled_antlers_views_in_statamic_storage()
    {
        $antlers = 'Hello {{ name }}';
        $viewName = 'antlers_'.sha1($antlers);

        $compiled = AntlersBladePrecompiler::compile("@antlers{$antlers}@endantlers");

        $this->assertSame("@include('compiled__views::{$viewName}')", $compiled);
        $this->assertSame($antlers, file_get_contents(storage_path("statamic/tmp/nocache/{$viewName}.antlers.html")));
    }
}
