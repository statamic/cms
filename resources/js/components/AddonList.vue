<template>
    <data-list :columns="[]" :rows="rows" :visible-columns="[]" :search-query="searchQuery" v-if="loaded">
        <div class="" slot-scope="{ rows: addons }">
            <div class="data-list-header flex items-center card p-0">
                <data-list-search class="flex-1" v-model="searchQuery"></data-list-search>
                <div class="filter bg-white ml-3 mb-0">
                    <a href="" class="active">Not Installed</a>
                    <a href="">Installed</a>
                    <a href="">All</a>
                </div>
            </div>
            <div class="addon-grid my-4">
                <div class="addon-card bg-white text-grey-dark h-full shadow rounded cursor-pointer" v-for="addon in addons" @click="showAddon(addon)">
                    <div class="h-64 rounded-t bg-cover" :style="'background-image: url(\''+getCover(addon)+'\')'"></div>
                    <div class="px-3 mb-2 relative text-center">
                        <a :href="addon.seller.website" class="relative">
                            <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full h-14 w-14 z-30 bg-white relative -mt-4 border-2 border-white">
                        </a>
                        <div class="addon-card-title mb-2 text-lg font-bold text-center">{{ addon.name }}</div>
                        <p v-text="addon.variants[0].summary" class="text-sm"></p>
                    </div>

                    <portal to="modals" v-if="showingAddon">
                        <modal name="addon-modal" height="auto" :scrollable="true" width="760px" :adaptive="true" :pivotY=".1">
                            <img :src="getCover(addon)" class="rounded-t">
                            <div class="flex items-center justify-between px-4 py-2 mb-2 border-b">
                                <a :href="addon.seller.website" class="relative flex items-center">
                                    <img :src="this.domain+'/images/storage/'+addon.seller.avatar" :alt="addon.seller.name" class="rounded-full h-14 w-14 mr-2">
                                    <span class="font-bold">{{ addon.seller.name }}</span>
                                </a>
                                <button class="btn">
                                    Install Addon
                                </button>
                            </div>
                            <div class="p-4">
                                {{ addon.variants[0].description }}
                            </div>
                        </modal>
                    </portal>

                </div>
            </div>
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
            loaded: false,
            showingAddon: false
        }
    },

    computed: {
        api() {
            return this.domain + '/api/v1/marketplace';
        }
    },

    created() {
        this.rows = this.getAddons()
    },

    methods: {
        getAddons() {
            this.axios.get(this.api + this.endpoints.addons).then(response => {
                this.rows = response.data.data;
                this.loaded = true;
            });
        },

        getCover(addon) {
            return (addon.variants[0].assets.length) ? addon.variants[0].assets[0].url : '';
        },

        showAddon(addon) {
            this.showingAddon = true;
            this.$modal.show('addon-modal');
        }
    }
}
</script>
