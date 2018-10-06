<template>
    <div>
        <div class="flex mb-3">
            <h1 class="flex-1">
                Updates
                <span v-if="currentVersion" class="text-sm text-grey-light italic ml-3">
                    Current Version: {{ currentVersion }}
                </span>
            </h1>
            <button v-if="(output || lastInstallLog) && ! modalOpen" class="btn" @click="$modal.show('output-modal')">
                <template v-if="output.processing">
                    {{ output.status }}
                    <span class="icon icon-circular-graph animation-spin ml-1"></span>
                </template>
                <template v-else>Last Install Log</template>
            </button>
            <button v-if="showActions" class="btn ml-2" @click="updateToLatest()">{{ translate('Update to Latest') }}</button>
        </div>

        <div v-for="release in changelog" class="card tight update-release shadow mb-5">
            <div class="card-heading clearfix mb-4">
                <template v-if="showActions">
                    <button v-if="release.type === 'current'" class="btn float-right opacity-50" disabled>Current Version</button>
                    <button v-else-if="release.latest" @click="updateToLatest()" class="btn float-right">Update to Latest</button>
                    <button v-else @click="installExplicitVersion(release.version)" class="btn float-right">
                        <template v-if="release.type === 'upgrade'">Upgrade to</template>
                        <template v-if="release.type === 'downgrade'">Downgrade to</template>
                        {{ release.version }}
                    </button>
                </template>

                <h1>{{ release.version }}</h1>
                <h5 class="date">Released on {{ release.date }}</h5>
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
                <composer-output :title="output.status" class="m-3"></composer-output>
            </modal>
        </portal>

        <portal to="updates-badge" v-if="currentVersion && changelog[0].version != currentVersion">
            <span class="badge bg-red text-white ml-1 rounded-full px-1">1</span>
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

            showActions() {
                return ! this.gettingChangelog && ! this.output.processing;
            }
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

                axios.get('/cp/updater/changelog').then(response => {
                    this.gettingChangelog = false;
                    this.changelog = response.data.changelog;
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
                this.getChangelog();

                this.output = {
                    processing: false,
                    status: 'Installation complete!'
                };
            },
        }
    }
</script>
