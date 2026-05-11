<template>
    <div class="max-w-page mx-auto">
        <ui-header :title="name" icon="updates">
            <template v-if="!gettingChangelog" #actions>
                {{ currentVersion }}
                <ui-badge v-if="onLatestVersion" :text="__('Up to date')" color="green" size="lg" icon="checkmark" />
                <ui-badge v-else-if="securityUpdateAvailable" :text="__('Security update available')" color="red" size="lg" icon="alert-warning-exclamation-mark" />
                <ui-badge v-else :text="__('Update available')" color="amber" size="lg" icon="alert-warning-exclamation-mark" />
            </template>
        </ui-header>

        <ui-card v-if="gettingChangelog" class="text-center starting-style-transition" v-cloak>
            <Icon name="loading" />
        </ui-card>

        <div
            class="mb-6 flex cursor-pointer items-center justify-between rounded-sm border border-dashed border-yellow-dark bg-yellow p-4 text-xs"
            v-if="!showingUnlicensedReleases && hasUnlicensedReleases"
            @click="showingUnlicensedReleases = true"
        >
            <div>
                <h4 v-text="__('messages.addon_has_more_releases_beyond_license_heading')" />
                <p v-text="__('messages.addon_has_more_releases_beyond_license_body')" />
            </div>
            <ui-button size="sm" v-text="__('View additional releases')" />
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

        <Pagination
            v-if="meta.last_page > 1"
            class="mt-6"
            :resource-meta="meta"
            :per-page="perPage"
            @page-selected="setPage"
            @per-page-changed="setPerPage"
        />
    </div>
</template>

<script>
import Release from './Release.vue';
import { Icon, Pagination } from '@/components/ui';

export default {
    components: {
        Release,
        Icon,
        Pagination,
    },

    props: ['slug', 'package', 'name'],

    data() {
        return {
            gettingChangelog: true,
            changelog: [],
            currentVersion: null,
            latestRelease: null,
            showingUnlicensedReleases: false,
            page: 1,
            perPage: 10,
            meta: {},
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

        securityUpdateAvailable() {
            return this.currentVersion && this.changelog
                .filter((release) => release.type === 'upgrade')
                .some((release) => release.security);
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

            this.$axios
                .get(cp_url(`/updater/${this.slug}/changelog`), {
                    params: {
                        page: this.page,
                        perPage: this.perPage,
                    },
                })
                .then((response) => {
                    this.gettingChangelog = false;
                    this.changelog = response.data.changelog;
                    this.currentVersion = response.data.currentVersion;
                    this.meta = response.data.meta;

                    if (this.page === 1 && response.data.changelog.length > 0) {
                        this.latestRelease = response.data.changelog[0];
                    }
                });
        },

        setPage(page) {
            this.page = page;
            this.getChangelog();
        },

        setPerPage(perPage) {
            this.perPage = perPage;
            this.page = 1;
            this.getChangelog();
        },
    },
};
</script>
