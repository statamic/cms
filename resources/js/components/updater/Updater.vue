<template>
    <div>
        <div class="mb-6 flex items-center">
            <h1 class="flex-1">
                <span v-text="name" />
                <span v-if="currentVersion" class="font-normal text-gray-700 ltr:ml-2 rtl:mr-2">{{
                    currentVersion
                }}</span>
            </h1>
            <button v-if="!onLatestVersion" class="btn-primary ltr:ml-4 rtl:mr-4" @click="modalOpen = true">
                {{ __('Update') }}
            </button>
            <div v-if="onLatestVersion" v-text="__('Up to date')" />
        </div>

        <div v-if="gettingChangelog" class="card p-6 text-center">
            <loading-graphic />
        </div>

        <div
            class="mb-6 flex cursor-pointer items-center justify-between rounded-sm border border-dashed border-yellow-dark bg-yellow p-4 text-xs"
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
                :package="package"
                :show-actions="showActions"
            />
        </template>

        <release
            v-for="release in licensedReleases"
            :key="release.version"
            :release="release"
            :package-name="name"
            :package="package"
            :show-actions="showActions"
        />

        <confirmation-modal v-if="modalOpen" :cancellable="false" :button-text="__('OK')" @confirm="modalOpen = false">
            <div class="prose">
                <p v-text="`${__('messages.updater_update_to_latest_command')}:`" />
                <code-block copyable :text="`composer update ${package}`" />
                <p v-html="link"></p>
            </div>
        </confirmation-modal>
    </div>
</template>

<script>
import Release from './Release.vue';

export default {
    components: {
        Release,
    },

    props: ['slug', 'package', 'name'],

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
            return { timeout: Statamic.$config.get('ajaxTimeout') };
        },

        showActions() {
            return !this.gettingChangelog;
        },

        onLatestVersion() {
            return this.currentVersion && this.currentVersion == this.latestVersion;
        },

        licensedReleases() {
            return this.changelog.filter((release) => release.licensed);
        },

        unlicensedReleases() {
            return this.changelog.filter((release) => !release.licensed);
        },

        hasUnlicensedReleases() {
            return this.unlicensedReleases.length > 0;
        },

        latestVersion() {
            return this.latestRelease && this.latestRelease.version;
        },

        link() {
            return (
                __('Learn more about :link', {
                    link: `<a href="https://statamic.dev/updating" target="_blank">${__('Updates')}</a>`,
                }) + '.'
            );
        },
    },

    created() {
        this.getChangelog();
    },

    methods: {
        getChangelog() {
            this.gettingChangelog = true;

            this.$axios.get(cp_url(`/updater/${this.slug}/changelog`)).then((response) => {
                this.gettingChangelog = false;
                this.changelog = response.data.changelog;
                this.currentVersion = response.data.currentVersion;
                this.latestRelease = response.data.changelog[0];
            });
        },
    },
};
</script>
