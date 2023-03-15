<template>
    <div>
        <div class="flex items-center mb-6">
            <h1 class="flex-1">
                <span v-text="name" />
                <span v-if="currentVersion" class="font-normal text-gray-700">{{ currentVersion }}</span>
            </h1>
            <button v-if="!onLatestVersion" class="btn-primary ml-4" @click="showUpdateInstructions()">{{ __('Update') }}</button>
            <div v-if="onLatestVersion" v-text="__('Up to date')" />
        </div>

        <div v-if="gettingChangelog" class="card p-6 text-center">
            <loading-graphic  />
        </div>

        <div class="bg-yellow border-yellow-dark border-dashed p-4 text-xs border mb-6 rounded cursor-pointer flex items-center justify-between"
            v-if="!showingUnlicensedReleases && hasUnlicensedReleases"
            @click="showingUnlicensedReleases = true"
        >
            <div>
                <h4 v-text="__('messages.addon_has_more_releases_beyond_license_heading')" />
                <p v-text="__('messages.addon_has_more_releases_beyond_license_body')" />
            </div>
            <button class="btn btn-xs" v-text="__('View additional releases')" />
        </div>

        <template v-if="showingUnlicensedReleases">
            <release
                v-for="release in unlicensedReleases"
                :key="release.version"
                :release="release"
                :package-name="name"
                :show-actions="showActions"
            />
        </template>

        <release
            v-for="release in licensedReleases"
            :key="release.version"
            :release="release"
            :package-name="name"
            :show-actions="showActions"
        />

        <modal
            v-if="modalOpen"
            name="show-update-instructions"
            v-slot="{ close: closeModal }"
            :pivot-y="0.5"
            :overflow="false"
            width="25%"
            @closed="modalOpen = false"
        >
            <div class="p-6 relative">
                To update to the lastest version please run:
                <code class="inline-block my-2">composer update <span v-text="package" /></code>
                Learn more about <a href="https://statamic.dev/updating">Updating</a>
                <button
                    class="btn-close absolute top-0 right-0 mt-4 mr-4"
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
                modalOpen: false,
                latestRelease: null,
                showingUnlicensedReleases: false,
            };
        },

        computed: {
            toEleven() {
                return {timeout: Statamic.$config.get('ajaxTimeout')};
            },

            showActions() {
                return ! this.gettingChangelog;
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
        },

        created() {
            this.getChangelog();
        },

        methods: {
            getChangelog() {
                this.gettingChangelog = true;

                this.$axios.get(cp_url(`/updater/${this.slug}/changelog`)).then(response => {
                    this.gettingChangelog = false;
                    this.changelog = response.data.changelog;
                    this.currentVersion = response.data.currentVersion;
                    this.latestRelease = response.data.changelog[0];
                });
            },

            showUpdateInstructions() {
                this.modalOpen = true;
            },
        }
    }
</script>
