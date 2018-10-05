<template>
    <div>
        <div class="flex mb-3">
            <h1 class="flex-1">
                Updates
                <span v-if="currentVersion" class="text-sm text-grey-light italic ml-3">
                    Current Version: {{ currentVersion }}
                </span>
            </h1>
            <button v-if="(output || lastInstallLog) && ! modalOpen" class="btn mr-2" @click="$modal.show('output-modal')">
                <template v-if="output.processing">
                    {{ output.status }}
                    <span class="icon icon-circular-graph animation-spin ml-1"></span>
                </template>
                <template v-else>Last Install Log</template>
            </button>
            <button class="btn" @click="updateToLatest()">{{ translate('Update to Latest') }}</button>
        </div>

        <ul v-for="(changes, version) in changelog" class="mt-3">
            <p><strong>{{ version }}</strong></p>
            <button v-if="version == latest" @click="updateToLatest()" class="btn">Update to latest version!</button>
            <button v-else @click="installExplicitVersion(version)" class="btn">Install {{ version }}</button>
            <li v-for="item in changes">[{{ item.type }}] {{ item.change }}</li>
        </ul>

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
                <composer-output :title="output.status" class="m-3"></composer-output>
            </modal>
        </portal>

    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        data() {
            return {
                changelog: [],
                currentVersion: null,
                lastInstallLog: null,
                output: false,
                modalOpen: false,
            };
        },

        props: {
            ajaxTimeout: {
                type: Number,
                default: 600000,
            }
        },

        computed: {
            toEleven() {
                return {timeout: this.ajaxTimeout};
            },

            latest() {
                return _.chain(this.changelog).keys().first();
            },
        },

        mounted() {
            this.getCurrentVersion();
            this.getChangelog();
        },

        created() {
            this.$events.$on('composer-finished', this.composerFinished);
        },

        methods: {
            getChangelog() {
                axios.get('/cp/updater/changelog').then(response => {
                    this.changelog = response.data;
                });
            },

            getCurrentVersion() {
                axios.get('/cp/updater/version').then(response => {
                    this.currentVersion = response.data.currentVersion;
                    this.lastInstallLog = response.data.lastInstallLog;
                });
            },

            update() {
                axios.post('/cp/updater/update', {}, this.toEleven);

                this.output = {
                    processing: true,
                    status: 'Updating statamic/cms'
                };

                this.$modal.show('output-modal');
            },

            updateToLatest() {
                axios.post('/cp/updater/update-to-latest', {}, this.toEleven);

                this.output = {
                    processing: true,
                    status: 'Installing latest statamic/cms version'
                };

                this.$modal.show('output-modal');
            },

            installExplicitVersion(version) {
                axios.post('/cp/updater/install-explicit-version', {'version': version}, this.toEleven);

                this.output = {
                    processing: true,
                    status: 'Installing statamic/cms version ' + version
                };

                this.$modal.show('output-modal');
            },

            composerFinished() {
                this.getCurrentVersion();

                this.output = {
                    processing: false,
                    status: 'Installation complete!'
                };
            },
        }
    }
</script>
