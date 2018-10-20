<template>
    <data-list :columns="[]" :rows="rows" :visible-columns="[]" v-if="loaded">
        <div class="" slot-scope="{ rows: addons }">
            <div class="data-list-header flex items-center card p-0">
                <data-list-search class="flex-1" v-model="searchQuery"></data-list-search>
                <div class="filter bg-white ml-3 mb-0">
                    <a @click="setFilter('installable')" :class="{ active: filter == 'installable' }">Not Installed</a>
                    <a @click="setFilter('installed')" :class="{ active: filter == 'installed' }">Installed</a>
                    <a @click="setFilter('all')" :class="{ active: filter == 'all' }">All</a>
                </div>
            </div>
            <div class="addon-grid my-4">
                <div class="addon-card bg-white text-grey-dark h-full shadow rounded cursor-pointer" v-for="addon in addons" :key="addon.id" @click="showAddon(addon)">
                    <div class="h-64 rounded-t bg-cover" :style="'background-image: url(\''+getCover(addon)+'\')'"></div>
                    <div class="px-3 mb-2 relative text-center">
                        <a :href="addon.seller.website" class="relative">
                            <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full h-14 w-14 z-30 bg-white relative -mt-4 border-2 border-white">
                        </a>
                        <div class="addon-card-title mb-2 text-lg font-bold text-center">{{ addon.name }}</div>
                        <p v-text="addon.variants[0].summary" class="text-sm"></p>
                    </div>
                </div>

            </div>

            <!-- I see there's a pagination component, maybe I could tie into that instead -->
            <template v-if="pagination.links">
                <button class="btn" @click="page--; getAddons">Previous Page</button>
                <button class="btn" @click="page++; getAddons">Next Page</button>
            </template>

            <portal to="modals" v-if="showingAddon">
                <modal name="addon-modal" height="auto" :scrollable="true" width="760px" :adaptive="true" :pivotY=".1">
                    <addon-details
                        :addon="showingAddon"
                        :cover="getCover(showingAddon)">
                    </addon-details>
                </modal>
            </portal>
        </div>
    </data-list>
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
    import axios from 'axios';

    export default {

        props: [
            'domain',
            'endpoints'
        ],

        data() {
            return {
                loaded: false,
                rows: [],
                pagination: {},
                searchQuery: '',
                filter: 'installable',
                page: 1,
                showingAddon: false,
            }
        },

        computed: {
            params() {
                return {
                    page: this.page,
                    filter: this.filter,
                    q: this.searchQuery,
                };
            }
        },

        created() {
            this.rows = this.getAddons();

            this.$events.$on('composer-finished', this.getAddons);
        },

        watch: {
            page() {
                this.getAddons();
            },

            searchQuery() {
                this.page = 1;

                this.$nextTick(function () {
                    this.getAddons();
                });
            },
        },

        methods: {
            getAddons() {
                axios.get('/cp/api/addons', {'params': this.params}).then(response => {
                    this.loaded = true;
                    this.rows = response.data.data;
                    this.pagination.links = response.data.links;
                    this.pagination.meta = response.data.meta;

                    if (this.showingAddon) {
                        this.refreshShowingAddon();
                    }
                });
            },

            refreshShowingAddon() {
                this.showingAddon.installed = _.find(this.rows, {id: this.showingAddon.id}).installed;

                this.$events.$emit('addon-refreshed');
            },

            getCover(addon) {
                return addon.variants[0].assets.length
                    ? addon.variants[0].assets[0].url
                    : 'https://statamic.com/images/img/marketplace/placeholder-addon.png';
            },

            showAddon(addon) {
                this.showingAddon = addon;

                this.$nextTick(() => {
                    this.$modal.show('addon-modal');
                });
            },

            setFilter(filter) {
                if (this.filter === filter) {
                    return;
                }

                this.page = 1;
                this.filter = filter;

                this.getAddons();
            },
        }
    }
</script>
