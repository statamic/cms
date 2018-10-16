<template>
    <div>
        <div class="flex mb-3">
            <h1 class="flex-1">
                Updates
                <span v-if="currentVersion" class="text-sm text-grey-light italic ml-3">
                    Current Version: {{ currentVersion }}
                </span>
            </h1>
            <button v-if="(composer.processing || lastInstallLog) && ! modalOpen" class="btn" @click="$modal.show('output-modal')">
                <template v-if="composer.processing">
                    {{ composer.status }}
                    <span class="icon icon-circular-graph animation-spin ml-1"></span>
                </template>
                <template v-else>Last Install Log</template>
            </button>
            <button v-if="showActions && ! onLatestVersion" class="btn btn-primary ml-2" @click="updateToLatest()">{{ translate('Update to Latest') }}</button>
        </div>

        <div class="card mb-5 text-grey-light flex items-center" v-if="onLatestVersion">
            <svg version="1.0"
                class="fill-current mr-2"
                 xmlns="http://www.w3.org/2000/svg" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/"
                 x="0px" y="0px" width="80px" height="60px" viewBox="0 0 95.9 76.4" style="enable-background:new 0 0 95.9 76.4;"
                 xml:space="preserve">
                <g>
                    <path fill="fill-current" d="M26.5,73.1c0,2.1-1.7,3.3-3.9,3.3h-2.5c-7.7,0-12-3.4-12-12.1V50.1c0-5.3-2.5-7.6-5.2-8.4
                        C0.8,41.1,0,40,0,38.2c0-1.9,0.7-2.9,2.9-3.6c2.6-1,5.2-3.6,5.2-9.4V12.7C8.1,4.5,11.1,0,20.5,0h2.3c2.2,0,3.7,1.3,3.7,3.2
                        c0,2.2-1.4,3.5-3.6,3.5h-0.7c-5.1,0-6.9,2.2-6.9,8.4v12.8c0,5.8-2.7,9.7-6.7,10.2c3.8,0.4,6.7,3.3,6.7,10.2v13
                        c0,5.9,2.2,8.3,7.2,8.3C25.1,69.7,26.5,70.9,26.5,73.1z"/>
                    <path fill="fill-current" d="M33.7,48c0.6-0.8,1.3-1.2,2.2-1.2c0.9,0,1.8,0.4,2.7,1c2.9,1.9,6.1,3,10,3c3,0,5.3-1.3,5.3-3.8
                        c0-6.6-21.8-3-21.8-17.7C32,21.4,38.5,17,46.8,17c5.9,0,10.2,1.7,13.5,3.8c0.8,0.5,1.3,1.6,1.3,2.7c0,0.6-0.2,1.3-0.5,1.8l-1,1.4
                        c-0.7,1-1.5,1.4-2.5,1.4c-0.7,0-1.4-0.3-2.2-0.6c-2.4-1.3-4.9-1.9-8-1.9c-3.1,0-5,1.7-5,3.5c0,6.7,21.8,3.1,21.8,17.3
                        c0,8-6.5,12.9-16.4,12.9c-6.3,0-10.9-1.9-15.1-5.6c-0.7-0.6-1-1.4-1-2.2c0-0.6,0.2-1.3,0.6-1.9L33.7,48z"/>
                    <path fill="fill-current" d="M69.4,73.1c0-2.2,1.4-3.4,4-3.4c4.9,0,7.2-2.3,7.2-8.3v-13c0-6.9,3-9.8,6.7-10.2c-4-0.5-6.7-4.4-6.7-10.2V15.1
                        c0-6.1-1.8-8.4-6.9-8.4H73c-2.2,0-3.6-1.3-3.6-3.5c0-2,1.5-3.2,3.7-3.2h2.3c9.3,0,12.4,4.5,12.4,12.7v12.5c0,5.8,2.6,8.4,5.2,9.4
                        c2.2,0.7,2.9,1.7,2.9,3.6c0,1.8-0.8,2.9-2.9,3.5c-2.7,0.8-5.2,3.1-5.2,8.4v14.1c0,8.7-4.3,12.1-12,12.1h-2.5
                        C71.1,76.4,69.4,75.1,69.4,73.1z"/>
                </g>
            </svg>
            <div>
                <h1 class="mb-sm text-grey">Excellent. You are running the latest version of Statamic.</h1>
                <div class="text-sm">Version: <span class="text-grey">{{ currentVersion }}</span></div>
            </div>
        </div>

        <div v-for="release in changelog" class="card update-release mb-5">
            <div class="flex justify-between mb-4">
                <div>
                    <h1>{{ release.version }}</h1>
                    <h5 class="date">Released on {{ release.date }}</h5>
                </div>
                <div v-if="showActions">
                    <button v-if="release.type === 'current'" class="btn opacity-50" disabled>Current Version</button>
                    <button v-else-if="release.latest" @click="updateToLatest()" class="btn">Update to Latest</button>
                    <button v-else @click="installExplicitVersion(release.version)" class="btn">
                        <template v-if="release.type === 'upgrade'">Upgrade to</template>
                        <template v-if="release.type === 'downgrade'">Downgrade to</template>
                        {{ release.version }}
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div v-html="release.body"></div>
            </div>
        </div>

        <portal to="modals">
            <modal
                name="output-modal"
                height="auto"
                :scrollable="true"
                :pivotY=".1"
                width="760px"
                @opened="modalOpen = true; $events.$emit('start-composer')"
                @closed="modalOpen = false"
            >
                <composer-output class="m-3"></composer-output>
            </modal>
        </portal>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        data() {
            return {
                gettingChangelog: false,
                changelog: [],
                currentVersion: null,
                lastInstallLog: null,
                modalOpen: false,
                onLatestVersion: false
            };
        },

        props: {
            package: {
                type: String,
                required: true,
            },

            ajaxTimeout: {
                type: Number,
                default: 600000,
            }
        },

        computed: {
            toEleven() {
                return {timeout: this.ajaxTimeout};
            },

            composer() {
                return this.$store.state.statamic.composer;
            },

            showActions() {
                return ! this.gettingChangelog && ! this.composer.processing;
            },
        },

        mounted() {
            this.getChangelog();
        },

        created() {
            this.$events.$on('composer-finished', this.composerFinished);
        },

        methods: {
            getChangelog() {
                this.gettingChangelog = true;

                axios.get('/cp/updater/statamic/changelog').then(response => {
                    this.gettingChangelog = false;
                    this.changelog = response.data.changelog;
                    this.currentVersion = response.data.currentVersion;
                    this.lastInstallLog = response.data.lastInstallLog;
                });
            },

            update() {
                axios.post('/cp/updater/statamic/update', {}, this.toEleven);

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Updating ' + this.package,
                    package: this.package,
                });

                this.$modal.show('output-modal');
            },

            updateToLatest() {
                axios.post('/cp/updater/statamic/update-to-latest', {}, this.toEleven);

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Installing latest ' + this.package + ' version',
                    package: this.package,
                });

                this.$modal.show('output-modal');
            },

            installExplicitVersion(version) {
                axios.post('/cp/updater/statamic/install-explicit-version', {'version': version}, this.toEleven);

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Installing ' + this.package + ' version ' + version,
                    package: this.package,
                });

                this.$modal.show('output-modal');
            },

            composerFinished() {
                this.getChangelog();

                this.$store.commit('statamic/composer', {
                    processing: false,
                    status: 'Installation complete!',
                    package: this.package,
                });

                this.$events.$emit('recount-updates');
            },
        }
    }
</script>
