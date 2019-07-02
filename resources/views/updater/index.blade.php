@extends('statamic::layout')
@section('title', __('Updater'))

@section('content')

    @include('statamic::updater.partials.header')

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
