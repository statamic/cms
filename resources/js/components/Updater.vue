<template>
    <div>
        <p v-if="currentVersion">
            <strong>statamic/cms</strong> (installed version: {{ currentVersion }})
        </p>
        <ul v-for="(changes, version) in changelog">
            <strong>{{ version }}</strong>
            <li v-for="item in changes">[{{ item.type }}] {{ item.change }}</li>
            <template v-if="! output.processing">
                <button v-if="version == latest" @click="updateToLatest()">Update to latest version!</button>
                <button v-else @click="installExplicitVersion(version)">Install {{ version }}</button>
            </template>
        </ul>
        <composer-output v-show="output">{{ output.status }}</composer-output>
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
            this.$root.$on('composer-finished', this.composerFinished);
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

                this.$root.$emit('start-composer');

                this.output = {
                    processing: true,
                    status: 'Updating statamic/cms'
                };
            },

            updateToLatest() {
                axios.post('/cp/updater/update-to-latest', {}, this.toEleven);

                this.$root.$emit('start-composer');

                this.output = {
                    processing: true,
                    status: 'Installing latest statamic/cms version'
                };
            },

            installExplicitVersion(version) {
                axios.post('/cp/updater/install-explicit-version', {'version': version}, this.toEleven);

                this.$root.$emit('start-composer');

                this.output = {
                    processing: true,
                    status: 'Installing statamic/cms version ' + version
                };
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
