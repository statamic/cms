<template>
    <div>
        <div class="breadcrumb flex" v-if="showingAddon">
            <button
                @click="showingAddon = false"
                class="-m-2 flex flex-initial items-center p-2 text-xs text-gray-700 hover:text-gray-900 dark:text-dark-175 dark:hover:text-dark-100"
            >
                <svg-icon name="micro/chevron-right" class="h-6 w-4 rotate-180" />
                <span v-text="__('Addons')" />
            </button>
        </div>

        <div class="mb-6 flex" v-if="!showingAddon">
            <h1 class="flex-1" v-text="__('Addons')" />
        </div>

        <div v-if="error" class="card text-sm">
            {{ __('messages.addon_list_loading_error') }}
        </div>

        <div v-if="initializing" class="card p-6 text-center">
            <loading-graphic />
        </div>

        <data-list :rows="rows" v-if="!initializing && !showingAddon" v-slot="{ rows: addons }">
            <div class="">
                <div class="card p-0">
                    <div class="border-b px-4 text-sm dark:border-dark-900">
                        <button
                            class="data-list-filter-link"
                            :class="{ active: filter === 'all' }"
                            @click="filter = 'all'"
                            v-text="__('All')"
                        />
                        <button
                            class="data-list-filter-link"
                            :class="{ active: filter === 'installed' }"
                            @click="filter = 'installed'"
                        >
                            {{ __('Installed') }}
                            <span class="badge" v-if="installCount">{{ installCount }}</span>
                        </button>
                    </div>

                    <div class="p-2">
                        <data-list-search ref="search" v-model="searchQuery" />
                    </div>
                </div>

                <div class="addon-grid my-8" :class="{ 'opacity-50': loading }">
                    <div
                        class="addon-card relative h-full cursor-pointer rounded bg-white text-gray-800 shadow dark:bg-dark-600 dark:text-dark-150 dark:shadow-dark"
                        v-for="addon in addons"
                        :key="addon.id"
                        @click="showAddon(addon)"
                    >
                        <span
                            class="badge absolute top-0 mt-2 ltr:left-0 ltr:ml-2 rtl:right-0 rtl:mr-2"
                            v-if="addon.installed"
                            >Installed</span
                        >
                        <div
                            class="h-48 rounded-t bg-cover bg-center"
                            :style="'background-image: url(\'' + getCover(addon) + '\')'"
                        ></div>
                        <div class="relative mb-4 px-6 text-center">
                            <a :href="addon.seller.website" class="relative">
                                <img
                                    :src="addon.seller.avatar"
                                    :alt="addon.seller.name"
                                    class="relative -mt-8 inline h-14 w-14 rounded-full border-2 border-white bg-white dark:border-dark-600 dark:bg-dark-600"
                                />
                            </a>
                            <div class="addon-card-title mb-2 text-center text-lg font-bold">{{ addon.name }}</div>
                            <p class="mb-4 text-gray dark:text-dark-175" v-text="getPriceRange(addon)" />
                            <p v-text="addon.summary" class="text-sm"></p>
                        </div>
                    </div>
                </div>

                <data-list-pagination :resource-meta="meta" @page-selected="setPage"></data-list-pagination>
            </div>
        </data-list>

        <template v-if="unlisted.length && !showingAddon">
            <h6 class="mt-8">{{ __('Unlisted Addons') }}</h6>
            <div class="card mt-2 p-0">
                <table class="data-table">
                    <tbody>
                        <tr v-for="addon in unlisted" :key="addon.package">
                            <td v-text="addon.name" />
                            <td v-text="addon.package" />
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <addon-details v-if="showingAddon" :addon="showingAddon" :cover="getCover(showingAddon)" />
    </div>
</template>

<style>
.addon-grid {
    display: grid;
    grid-gap: 32px;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    grid-auto-flow: dense;
}
</style>

<script>
export default {
    props: ['domain', 'endpoints', 'installCount'],

    data() {
        return {
            initializing: true,
            loading: true,
            rows: [],
            meta: {},
            searchQuery: '',
            filter: 'all',
            page: 1,
            showingAddon: false,
            error: false,
            unlisted: [],
        };
    },

    computed: {
        params() {
            return {
                page: this.page,
                q: this.searchQuery,
                installed: this.filter === 'installed' ? 1 : 0,
            };
        },

        loaded() {
            return !this.loading && !this.error;
        },
    },

    watch: {
        page() {
            this.getAddons();
        },

        searchQuery() {
            this.page = 1;
            this.getAddons();
        },

        filter() {
            this.page = 1;
            this.getAddons();
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading('addon-list', loading);
            },
        },
    },

    created() {
        this.rows = this.getAddons();

        this.$events.$on('composer-finished', this.getAddons);
    },

    methods: {
        getAddons() {
            this.loading = true;

            this.$axios
                .get(cp_url('/api/addons'), { params: this.params })
                .then((response) => {
                    this.loading = false;
                    this.initializing = false;
                    this.rows = response.data.data;
                    this.meta = response.data.meta;
                    this.unlisted = response.data.unlisted ?? [];

                    if (this.showingAddon) {
                        this.refreshShowingAddon();
                    }
                })
                .catch((e) => {
                    this.loading = false;
                    this.error = true;
                    this.$toast.error(__('Something went wrong'));
                });
        },

        setPage(page) {
            this.page = page;
        },

        refreshShowingAddon() {
            this.showingAddon = this.rows.find((row) => row.id === this.showingAddon.id);

            this.$events.$emit('addon-refreshed');
        },

        getCover(addon) {
            return addon.assets.length
                ? addon.assets[0].url
                : 'https://statamic.com/images/img/marketplace/placeholder-addon.png';
        },

        getPriceRange(addon) {
            let [low, high] = addon.price_range;
            low = low ? `$${low}` : 'Free';
            high = high ? `$${high}` : 'Free';
            return low == high ? low : `${low} - ${high}`;
        },

        showAddon(addon) {
            this.showingAddon = addon;
            window.scrollTo(0, 0);
        },
    },
};
</script>
