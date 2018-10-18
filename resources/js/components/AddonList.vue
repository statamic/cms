<template>
    <data-list :columns="searchableColumns" :rows="rows" :visible-columns="searchableColumns" :search-query="searchQuery" v-if="loaded">
        <div class="" slot-scope="{ rows: addons }">
            <div class="data-list-header flex items-center card p-0">
                <data-list-search class="flex-1" v-model="searchQuery"></data-list-search>
                <div class="filter bg-white ml-3 mb-0">
                    <a @click="filter = 'installable'" :class="{ active: filter == 'installable' }">Not Installed</a>
                    <a @click="filter = 'installed'" :class="{ active: filter == 'installed' }">Installed</a>
                    <a @click="filter = 'all'" :class="{ active: filter == 'all' }">All</a>
                </div>
            </div>
            <div class="addon-grid my-4">
                <div class="addon-card bg-white text-grey-dark h-full shadow rounded cursor-pointer" v-for="addon in filterAddons(addons)" :key="addon.id" @click="showAddon(addon)">
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
                rows: [],
                searchQuery: '',
                filter: 'installable',
                loaded: false,
                showingAddon: false,
                searchableColumns: [
                    'name',
                    'seller', // TODO?
                ],
            }
        },

        created() {
            this.rows = this.getAddons()

            this.$events.$on('composer-finished', this.getAddons);
        },

        methods: {
            getAddons() {
                this.axios.get('/cp/marketplace/approved-addons').then(response => {
                    this.rows = response.data.data;
                    this.loaded = true;

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

            filterAddons(addons) {
                if (this.filter === 'installable') {
                    return _.reject(addons, (addon) => addon.installed);
                } else if (this.filter === 'installed') {
                    return _.filter(addons, (addon) => addon.installed);
                }

                return addons;
            }
        }
    }
</script>
