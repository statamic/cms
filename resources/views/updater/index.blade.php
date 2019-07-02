@extends('statamic::layout')
@section('title', __('Updater'))

@section('content')
    <div class="card p-5 content">
        <div class="flex">
            <div class="max-w-lg pr-4">
                <h1 class="mb-2 text-3xl">Updates</h1>
                <p class="text-base text-grey-80 mb-3">From new features, enhancements, and performance improvements, to bug and security fixes, keeping Statamic up to date is an important part of maintaining your site. Updates are handled by <a href="https://getcomposer.org/" target="_blank">Composer</a> and may take anywhere from a few seconds to a few minutes to run.</p>
                <p><a href="" class="font-bold text-blue">Learn more about updates in the docs</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
            </div>
            <div class="p-2 text-center">
                @svg('marketing/shield-check')
            </div>
        </div>
    </div>

    <h6 class="mt-4">Core</h6>
    <div class="card p-0 mt-1">
        <table class="data-table">
            <tr>
                <td class="w-48"><a href="{{ route('statamic.cp.updater.product', 'statamic') }}" class="text-lg text-blue font-bold">Statamic</a></td>
                <td>3.0.2</td>
                <td class="text-right">Up to date</td>
            </tr>
        </table>
    </div>

    <h6 class="mt-4">Addons</h6>
    <div class="card p-0 mt-1">
        <table class="data-table">
            <tr>
                <td class="w-48"><a href="" class="text-blue font-bold mr-1">SEO Pro</a>
                <td>3.1.0</td>
                <td class="text-right">Up to date</td>
            </tr>
            <tr>
                <td class="w-48"><a href="" class="text-blue font-bold mr-1">Wanjangler 2000</a></td>
                <td>0.9.7</td>
                <td class="text-right"><span class="badge-sm bg-green btn-sm">41 Updates</span></td>
            </tr>
            <tr>
                <td class="w-48"><a href="" class="text-blue font-bold mr-1">Workshop</a></td>
                <td>3.0.0</td>
                <td class="text-right"><span class="badge-sm bg-green btn-sm">3 Updates</span></td>
            </tr>
        </table>
    </div>

    {{-- @foreach($addons as $repo => $slug)

    @endforeach --}}
@endsection
