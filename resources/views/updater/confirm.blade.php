@extends('statamic::layout')

@section('content')

<updater version-to="{{ $version }}" version-from="{{ STATAMIC_VERSION }}" inline-template>
    <div class="card update" v-cloak>
        <div class="info-blocks">

                @if($title == 'Upgrade')
                    <h2>{{ t('upgrade_to_version', ['version' => "Statamic v{$version}"]) }}</h2>
                @else
                    <h2>{{ t('downgrade_to_version', ['version' => "Statamic v{$version}"]) }}</h2>
                @endif

                <template v-if="! started">
                    <h3>{!! t('backup_reminder') !!}</h3>
                    <a href="" @click.prevent="start" class="btn btn-default btn-lg">{{ $title }}</a>
                </template>

                <dl v-if="started">
                    <dt>{{ t('backup') }}</dt>
                        <dd v-if="backingUp">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            {{ t('backing_up') }}
                            <small class="help-block">{!! t('backing_up_instructions') !!}</small>
                        </dd>
                        <dd v-if="backedUp">
                            <span class="icon icon-check text-success"></span>
                            {{ t('backed_up') }}.
                            <small class="help-block">@{{{ backupMessage }}}</small>
                        </dd>
                        <dd v-if="backupFailed" class="text-danger">
                            <span class="icon icon-cross"></span>
                            {{ t('backup_failed') }}
                        </dd>

                    <dt>{{ t('downloading_version', ['version' => $version]) }}</dt>
                        <dd v-if="downloading">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            {{ t('downloading') }}
                            <small class="help-block">{{ t('downloading_latest') }}</small>
                        </dd>
                        <dd v-if="downloaded">
                            <span class="icon icon-check text-success"></span>
                            {{ t('downloaded') }}
                            <small class="help-block">@{{{ downloadMessage }}}</small>
                        </dd>
                        <dd v-if="downloadFailed" class="text-danger">
                            <span class="icon icon-cross"></span>
                            {{ t('download_failed') }}
                        </dd>

                    <dt v-if="!hasErrors || cleanupFailed">{{ t('installation') }}</dt>
                    <dt v-if="hasErrors && !cleanupFailed" class="text-danger">{{ t('installation_has_failed') }}</dt>
                        <dd v-if="!installing && !hasErrors" class="no-icon">
                            {{ t('preparing_installation') }}
                        </dd>

                        <dd v-if="unzipping">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            {{ t('unzipping_files') }}
                            <small class="help-block">{{ t('unzipping_files_instructions') }}</small>
                        </dd>
                        <dd v-if="unzipped">
                            <span class="icon icon-check text-success"></span>
                            {{ t('unzipped_files') }}
                        </dd>

                        <dd v-if="installingDependencies">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            {{ t('installing_dependencies') }}
                            <small class="help-block">{{ t('installing_dependencies_instructions') }}</small>
                        </dd>
                        <dd v-if="installedDependencies">
                            <span class="icon icon-check text-success"></span>
                            {{ t('dependencies_installed') }}
                        </dd>

                        <dd v-if="swapping">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            {{ t('swapping_files') }}
                            <small class="help-block">{{ t('swapping_files_instructions') }}</small>
                        </dd>
                        <dd v-if="swapped">
                            <span class="icon icon-check text-success"></span>
                            {{ t('files_swapped') }}
                        </dd>

                        <dd v-if="updated">
                            <span class="icon icon-check text-success"></span>
                            <p v-if="updated" class="text-success">{{ t('now_running', ['version' => $version]) }}</p>
                        </dd>

                        <dd v-if="cleaningUp">
                            <span class="icon icon-circular-graph animation-spin"></span>
                            {{ t('cleaning_up') }}
                            <small class="help-block">{{ t('cleaning_up_instructions') }}</small>
                        </dd>
                        <dd v-if="cleanedUp">
                            <span class="icon icon-check text-success"></span>
                            <b>{{ t('update_complete') }}</b>
                        </dd>

                        <dd v-if="hasErrors" v-for="error in errors" class="text-danger">
                            <span class="icon icon-cross"></span>
                            @{{ error.message }}
                            <small class="help-block" v-if="error.e">
                                @{{ error.e }}
                            </small>
                        </dd>
                    </dd>
                </dl>

                <a v-if="updated" href="{{ route('dashboard') }}" class="btn btn-lg">{{ t('return_to_dashboard') }}</a>

                <audio ref="audio">
                    <source src="{{ cp_resource_url('audio/powerup.mp3') }}" type="audio/mp3">
                </audio>

        </div>
    </div>
</updater>

@endsection
