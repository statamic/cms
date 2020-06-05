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
                <h1 class="mb-sm">You are running the latest version.</h1>
                <h3>Version: {{ currentVersion }}</h3>
            </div>
            <svg-icon name="marketing/tooter-nay" class="w-16 h-16" />
        </div>

        <div class="bg-yellow border-yellow-dark border-dashed p-2 text-xs border mb-3 rounded cursor-pointer flex items-center justify-between"
            v-if="!showingUnlicensedReleases"
            @click="showingUnlicensedReleases = true"
        >
            <div>
                <h4>This addon has more releases beyond your licensed limit.</h4>
                <p>You may update, but will need to upgrade or purchase a new license.</p>
            </div>
            <button class="btn btn-sm">View additional releases</button>
        </div>

        <release
            v-if="showingUnlicensedReleases"
            v-for="release in unlicensedReleases"
            :key="release.version"
            :release="release"
            :show-actions="showActions"
        />

        <release
            v-for="release in licensedReleases"
            :key="release.version"
            :release="release"
            :show-actions="showActions"
        />

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
    import Release from './Release.vue';

    export default {

        components: {
            Release
        },

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
                latestVersion: null,
                showingUnlicensedReleases: false,
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

            onLatestVersion() {
                return this.currentVersion == this.latestVersion;
            },

            licensedReleases() {
                return this.changelog.filter(release => release.licensed);
            },

            unlicensedReleases() {
                return this.changelog.filter(release => !release.licensed);
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

                this.$axios.get(`/cp/updater/${this.slug}/changelog`).then(response => {
                    this.gettingChangelog = false;
                    this.changelog = response.data.changelog;
                    this.currentVersion = response.data.currentVersion;
                    this.latestVersion = response.data.changelog[0].version;
                    this.lastInstallLog = response.data.lastInstallLog;
                });
            },

            updateToLatest() {
                this.installExplicitVersion(this.latestVersion);
            },

            installExplicitVersion(version) {
                this.$axios.post(`/cp/updater/${this.slug}/install`, {'version': version}, this.toEleven);

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
