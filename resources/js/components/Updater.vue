<template>
    <div>
        <div class="flex mb-3">
            <h1 v-if="currentVersion" class="flex-1">
                {{ __('Current') }}: <span class="font-normal">{{ currentVersion }}</span>
            </h1>
            <button v-if="(composer.processing || lastInstallLog) && ! modalOpen" class="btn" @click="modalOpen = true">
                <template v-if="composer.processing">
                    {{ composer.status }}
                    <loading-graphic />
                </template>
                <template v-else>Last Install Log</template>
            </button>
            <button v-if="showActions && ! onLatestVersion" class="btn-primary ml-2" @click="updateToLatest()">{{ __('Update to Latest') }}</button>
        </div>

        <div v-if="gettingChangelog" class="card p-3 text-center">
            <loading-graphic  />
        </div>

        <div class="card mb-5 flex items-center" v-if="onLatestVersion">
            <div>
                <h1 class="mb-sm">You are running the latest version of Statamic.</h1>
                <h3>Version: {{ currentVersion }}</h3>
            </div>
            <svg-icon name="marketing/tooter-nay" class="w-16 h-16" />
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

        <modal
            v-if="modalOpen"
            name="output-modal"
            height="auto"
            :scrollable="true"
            :pivotY=".1"
            width="760px"
            :click-to-close="true"
            @opened="$events.$emit('start-composer')"
            @closed="modalOpen = false"
        >
            <composer-output :package="package" class="m-3"></composer-output>
        </modal>
    </div>
</template>

<script>
    export default {
        props: [
            'slug',
            'package',
            'name',
        ],

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

        computed: {
            toEleven() {
                return {timeout: Statamic.$config.get('ajaxTimeout')};
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

                this.$axios.get(`/cp/updater/${this.slug}/changelog`).then(response => {
                    this.gettingChangelog = false;
                    this.changelog = response.data.changelog;
                    this.currentVersion = response.data.currentVersion;
                    this.lastInstallLog = response.data.lastInstallLog;
                });
            },

            update() {
                this.$axios.post(`/cp/updater/${this.slug}/update`, {}, this.toEleven);

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Updating ' + this.package,
                    package: this.package,
                });

                this.modalOpen = true;
            },

            updateToLatest() {
                this.$axios.post(`/cp/updater/${this.slug}/update-to-latest`, {}, this.toEleven);

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Installing latest ' + this.package + ' version',
                    package: this.package,
                });

                this.modalOpen = true;
            },

            installExplicitVersion(version) {
                this.$axios.post(`/cp/updater/${this.slug}/install-explicit-version`, {'version': version}, this.toEleven);

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Installing ' + this.package + ' version ' + version,
                    package: this.package,
                });

                this.modalOpen = true;
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
