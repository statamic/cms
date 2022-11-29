<template>
    <div>
        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <span v-text="name" />
                <span v-if="currentVersion" class="font-normal text-grey-70">{{ currentVersion }}</span>
            </h1>
            <button v-if="(composer.processing || lastInstallLog) && ! modalOpen" class="btn" @click="modalOpen = true">
                <template v-if="composer.processing">
                    {{ composer.status }}
                    <loading-graphic />
                </template>
                <template v-else>{{ __('Last Install Log' ) }}</template>
            </button>
            <button v-if="canUpdateToLatestVersion" class="btn-primary ml-2" @click="updateToLatest()">{{ __('Update to Latest') }}</button>
            <div v-if="onLatestVersion" v-text="__('Up to date')" />
        </div>

        <div v-if="gettingChangelog" class="card p-3 text-center">
            <loading-graphic  />
        </div>

        <div class="bg-yellow border-yellow-dark border-dashed p-2 text-xs border mb-3 rounded cursor-pointer flex items-center justify-between"
            v-if="!showingUnlicensedReleases && hasUnlicensedReleases"
            @click="showingUnlicensedReleases = true"
        >
            <div>
                <h4 v-text="__('messages.addon_has_more_releases_beyond_license_heading')" />
                <p v-text="__('messages.addon_has_more_releases_beyond_license_body')" />
            </div>
            <button class="btn btn-sm" v-text="__('View additional releases')" />
        </div>

        <template v-if="showingUnlicensedReleases">
            <release
                v-for="release in unlicensedReleases"
                :key="release.version"
                :release="release"
                :package-name="name"
                :show-actions="showActions"
                @install="installExplicitVersion(release.version)"
            />
        </template>

        <release
            v-for="release in licensedReleases"
            :key="release.version"
            :release="release"
            :package-name="name"
            :show-actions="showActions"
            @install="installExplicitVersion(release.version)"
        />

        <modal
            v-if="modalOpen"
            name="updater-composer-output"
            v-slot="{ close: closeModal }"
            :close-on-click="!composer.processing"
            :pivot-y="0.5"
            :overflow="false"
            width="75%"
            @opened="$events.$emit('start-composer')"
            @closed="modalOpen = false"
        >
            <div class="p-3 relative">
                <composer-output :package="package" />
                <button
                    v-if="!composer.processing"
                    class="btn-close absolute top-0 right-0 mt-2 mr-2"
                    :aria-label="__('Close')"
                    @click="closeModal"
                    v-html="'&times'" />
            </div>
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
                gettingChangelog: true,
                changelog: [],
                currentVersion: null,
                lastInstallLog: null,
                modalOpen: false,
                latestRelease: null,
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
                return this.currentVersion && this.currentVersion == this.latestVersion;
            },

            licensedReleases() {
                return this.changelog.filter(release => release.licensed);
            },

            unlicensedReleases() {
                return this.changelog.filter(release => !release.licensed);
            },

            hasUnlicensedReleases() {
                return this.unlicensedReleases.length > 0;
            },

            latestVersion() {
                return this.latestRelease && this.latestRelease.version;
            },

            canUpdateToLatestVersion() {
                return this.latestVersion && this.latestVersion.canUpdate && this.showActions && ! this.onLatestVersion;
            }
        },

        created() {
            this.getChangelog();
            this.$events.$on('composer-finished', this.composerFinished);
        },

        methods: {
            getChangelog() {
                this.gettingChangelog = true;

                this.$axios.get(cp_url(`/updater/${this.slug}/changelog`)).then(response => {
                    this.gettingChangelog = false;
                    this.changelog = response.data.changelog;
                    this.currentVersion = response.data.currentVersion;
                    this.latestRelease = response.data.changelog[0];
                    this.lastInstallLog = response.data.lastInstallLog;
                });
            },

            updateToLatest() {
                this.installExplicitVersion(this.latestVersion);
            },

            installExplicitVersion(version) {
                this.$axios.post(cp_url(`/updater/${this.slug}/install`), {'version': version}, this.toEleven);

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: __('Installing :package version :version', {
                        package: this.package,
                        version: version
                    }),
                    package: this.package,
                });

                this.modalOpen = true;
            },

            composerFinished() {
                this.getChangelog();

                this.$store.commit('statamic/composer', {
                    processing: false,
                    status: __('Installation complete!'),
                    package: this.package,
                });

                this.$events.$emit('recount-updates');
            },
        }
    }
</script>
