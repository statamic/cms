@extends('layout')

@section('content')

<div class="update card">
    <h2>Update</h2>
    <dl>
        <dt>via Composer</dt>
        <dd>
            <span class="icon icon-install text-success"></span>
            <p>To update to the latest version, run the following command:</p>
            <p><code>composer update statamic/cms</code></p>
            <p class="mb-32"><small>or simply <code>composer update</code> to also update any of your manually required dependencies.</small></p>
        </dd>
    </dl>

    <p>Once that's done, you may refresh this page.</p>
    <p><a href="{{ route('dashboard') }}" class="btn btn-lg">{{ t('return_to_dashboard') }}</a></p>
</div>

@endsection
