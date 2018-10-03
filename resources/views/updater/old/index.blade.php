@extends('statamic::layout')

@section('content')

    <div class="flexy mb-3">
        <h1 class="fill">{{ __('Updater') }}</h1>
        @if (version_compare($latest->tag_name, STATAMIC_VERSION, '>'))
            <a href="{{ route('updater.update', $latest->tag_name) }}" class="btn btn-primary">{{ __('Update') }}</a>
        @else
            <a class="btn btn-primary" disabled href="">{{ __('Up to date') }}</a>
        @endif
    </div>

    @foreach ($releases as $release)
        <div class="card tight update-release shadow mb-5">
            <div class="card-heading clearfix">
                <h1>{{ $release->tag_name }}</h1>
                <h5 class="date">{{ __('Released on :date', ['date' => \Carbon\Carbon::parse($release->created_at)->format('F jS, Y')]) }}</h5>
            </div>
            <div class="card-body">
                {!! format_update($release->body) !!}
            </div>

        </div>
       @endforeach
@endsection
