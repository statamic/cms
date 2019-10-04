@extends('statamic::layout')
@section('title', __('Utilities'))

@section('content')

        <div class="content">
            <h1 class="mb">Utilities</h1>
        </div>

        <div class="flex flex-wrap -mx-2 mt-3">
            <div class="w-full md:w-1/3 px-2 mb-4">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-2">
                        <h2><a href="{{ cp_route('utilities.cache.index') }}" class="text-grey-90 hover:text-blue">Cache Manager</a></h2>
                        <p>Manage and view important information about Statamic's various caching layers.</p>
                        <p><a href="" class="font-bold text-blue">Read the docs</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                    </div>
                    <div class="flex p-2 border-t items-center">
                        <a href="{{ cp_route('utilities.cache.index') }}" class="font-bold text-blue text-sm hover:text-grey-90">View Cache Manager &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/3 px-2 mb-4">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-2">
                        <h2><a href="{{ cp_route('utilities.phpinfo') }}" class="text-grey-90 hover:text-blue">PHP Info</a></h2>
                        <p>Check your PHP configuration settings and installed modules.</p>
                        <p><a href="https://www.php.net/manual/en/function.phpinfo.php" class="font-bold text-blue">Learn more</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                    </div>
                    <div class="flex p-2 border-t items-center">
                        <a href="{{ cp_route('utilities.phpinfo') }}" class="font-bold text-blue text-sm hover:text-grey-90">View PHP Info&rarr;</a>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/3 px-2 mb-4">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-2">
                        <h2><a href="{{ cp_route('utilities.search') }}" class="text-grey-90 hover:text-blue">Search</a></h2>
                        <p>Manage and view important information about Statamic's search indexes.</p>
                        <p><a href="" class="font-bold text-blue">Read the docs</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                    </div>
                    <div class="flex p-2 border-t items-center">
                        <a href="{{ cp_route('utilities.search') }}" class="font-bold text-blue text-sm hover:text-grey-90">Search&rarr;</a>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/3 px-2 mb-4">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-2">
                        <h2><a href="{{ cp_route('utilities.email') }}" class="text-grey-90 hover:text-blue">Email</a></h2>
                        <p>Check email configuration and send a test.</p>
                        <p><a href="" class="font-bold text-blue">Read the docs</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                    </div>
                    <div class="flex p-2 border-t items-center">
                        <a href="{{ cp_route('utilities.email') }}" class="font-bold text-blue text-sm hover:text-grey-90">Email&rarr;</a>
                    </div>
                </div>
            </div>
        </div>

@endsection
