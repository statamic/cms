<template>
    <div>
        <div v-if="loading" class="card p-3 text-center">
            <loading-graphic  />
        </div>

        <data-list :rows="rows" v-if="!loading">
            <div class="" slot-scope="{ rows: addons }">

                <div class="card p-0">
                    <div class="border-b px-2 text-sm">
                        <button
                            class="data-list-filter-link"
                            :class="{ active: filter === 'all' }"
                            @click="filter = 'all'"
                            v-text="__('All')" />
                        <button
                            class="data-list-filter-link"
                            :class="{ active: filter === 'installed' }"
                            @click="filter = 'installed'"
                            v-text="__('Installed')" />
                    </div>

                    <div class="p-1">
                        <data-list-search
                            ref="search"
                            v-model="searchQuery" />
                    </div>
                </div>

                <div class="addon-grid my-4">
                    <div class="addon-card bg-white text-grey-80 h-full shadow rounded cursor-pointer relative" v-for="addon in addons" :key="addon.id" @click="showAddon(addon)">
                        <span class="badge absolute top-0 left-0 mt-1 ml-1" v-if="addon.installed">Installed</span>
                        <div class="h-64 rounded-t bg-cover" :style="'background-image: url(\''+getCover(addon)+'\')'"></div>
                        <div class="px-3 mb-2 relative text-center">
                            <a :href="addon.seller.website" class="relative">
                                <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full h-14 w-14 z-30 bg-white relative -mt-4 border-2 border-white inline">
                            </a>
                            <div class="addon-card-title mb-2 text-lg font-bold text-center">{{ addon.name }}</div>
                            <p v-text="addon.summary" class="text-sm"></p>
                        </div>
                    </div>

                </div>

                <data-list-pagination :resource-meta="meta" @page-selected="setPage"></data-list-pagination>

                <modal v-if="showingAddon" name="addon-modal" width="760px" :click-to-close="true" :scrollable="true" @closed="showingAddon = false">
                    <addon-details
                        :addon="showingAddon"
                        :cover="getCover(showingAddon)" />
                </modal>
            </div>
        </data-list>
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

        props: [
            'domain',
            'endpoints'
        ],

        data() {
            return {
                loading: true,
                rows: [],
                meta: {},
                searchQuery: '',
                filter: 'all',
                page: 1,
                showingAddon: false,
            }
        },

        computed: {
            params() {
                return {
                    page: this.page,
                    q: this.searchQuery,
                    installed: this.filter === 'installed' ? 1 : 0
                };
            }
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
                }
            },
        },

        created() {
            this.rows = this.getAddons();

            this.$events.$on('composer-finished', this.getAddons);
        },

        methods: {
            getAddons() {
                this.$axios.get(window.Statamic.$config.get('cpRoot')+'/api/addons', {'params': this.params}).then(response => {
                    this.loading = false;
                    this.rows = response.data.data;
                    this.meta = response.data.meta;

                    if (this.showingAddon) {
                        this.refreshShowingAddon();
                    }
                });
            },

            setPage(page) {
                this.page = page;
            },

            refreshShowingAddon() {
                this.showingAddon.installed = _.contains(this.meta.installed, this.showingAddon.variants[0].package);

                this.$events.$emit('addon-refreshed');
            },

            getCover(addon) {
                return addon.assets.length
                    ? addon.assets[0].url
                    : 'https://statamic.com/images/img/marketplace/placeholder-addon.png';
            },

            showAddon(addon) {
                this.showingAddon = addon;

                this.$nextTick(() => {
                    this.$modal.show('addon-modal');
                });
            },
        }
    }
</script>
