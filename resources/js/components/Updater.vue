<template>
    <div>
        <div class="flex mb-3">
            <h1 class="flex-1">Updater</h1>
            <button class="btn mr-2" v-if="output" @click="$modal.show('output-modal')">
                <span v-if="output.processing" class="icon icon-circular-graph animation-spin mr-1"></span>
                {{ output.status }}
            </button>
            <button class="btn" @click="updateToLatest()">{{ translate('Update to Latest') }}</button>
        </div>

        <p v-if="currentVersion" class="mb-3">
            <strong>statamic/cms</strong> (installed version: {{ currentVersion }})
        </p>
        <ul v-for="(changes, version) in changelog" class="mt-3">
            <p><strong>{{ version }}</strong></p>
            <template v-if="! output.processing">
                <button v-if="version == latest" @click="updateToLatest()" class="btn">Update to latest version!</button>
                <button v-else @click="installExplicitVersion(version)" class="btn">Install {{ version }}</button>
            </template>
            <li v-for="item in changes">[{{ item.type }}] {{ item.change }}</li>
        </ul>

        <portal to="modals">
            <modal name="output-modal" height="auto" :scrollable="true" :pivotY=".1" width="760px" @opened="$events.$emit('start-composer')">
                <composer-output class="m-3">
                    <p class="mb-2">{{ output.status }}</p>
                </composer-output>
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
                output: false,
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
                    this.currentVersion = response.data;
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
                    status: 'Operation complete!'
                };
            },
        }
    }
</script>
