<!doctype html>
<html lang="en">
    <head>
        @include('statamic::partials.head')
    </head>

    <body id="statamic" class="outside installer">

        <div class="container">
            <div class="logo">{!! inline_svg('statamic-mark') !!}</div>

            <installer cp-url="{{ route('cp') }}" inline-template>

                <div class="row" v-cloak>
                    <div class="col-md-4">

                        <div class="card flush">
                            <div class="head">
                                <h1>{{ t('progress') }}</h1>
                            </div>
                            <table class="control">
                                <tbody>
                                    <tr v-for="step in steps">
                                        <td>@{{ step.label }}</td>
                                        <td class="text-right">
                                            <template v-if="step.status === 'success'">✓</template>
                                            <template v-if="step.status === 'failure'">&times;</template>
                                            <template v-if="$key === currentStep && step.status === 'pending'">&rarr;</template>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="col-md-8">

                        <permissions v-if="currentStep === 'permissions'"></permissions>

                        <license-key v-if="currentStep === 'licenseKey'"></license-key>

                        <settings v-if="currentStep === 'settings'" :timezone='["{{ \Statamic\API\Config::get('system.timezone') }}"]'></settings>

                        <user v-if="currentStep === 'user'"></user>

                        <login v-if="currentStep === 'login'" :user-id="userId"></login>

                        <div class="card" v-if="cleanupFailed">
                            <div class="head">
                                <h1>{{ t('statamic_ready') }}</h1>
                            </div>
                            <hr>
                            <p class="text-danger">{!! t('delete_installer') !!}</p>
                            <p><a :href="cpUrl" class="btn btn-primary">{{ t('installer_deleted') }}</a></p>
                        </div>

                    </div>
                </div>

            </installer>
        </div>
        <script>Statamic.translations = {!! $translations !!};</script>
        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
