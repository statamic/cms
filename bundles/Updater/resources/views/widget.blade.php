<div class="card flush">
    <div class="head">
        <h1>{{ t('system_updates') }}</h1>
    </div>
    <div class="card-body pad-16 text-center flexy column">
        @if ($success)
            @if ($update_available)
                <h2>{{ translate_choice('cp.updates_available', $updates, ['updates' => $updates]) }}!</h2>
                @can('updater:update')
                    <a href="{{ route('updater') }}" class="btn mv-16 btn-small btn-primary">{{ t('upgrade_to_latest')}}</a>
                @endcan
            @else
                <h2>{{ t('on_latest') }}</h2>
            @endif
        @else
            <h2>{{ t('couldnt_fetch_updates')  }}</h2>
        @endif
    </div>
</div>
