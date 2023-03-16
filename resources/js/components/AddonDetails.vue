<template>
    <div>
        <div class="flex items-center mb-6">
            <h1 class="flex-1" v-text="addon.name" />
            <a :href="addon.url" target="_blank" class="btn mr-4" v-text="__('View on Marketplace')" />
            <button v-if="addon.installed" class="btn" @click="showComposerInstructions" v-text="__('Uninstall')" />
            <button v-else class="btn btn-primary" @click="showComposerInstructions" v-text="__('Install')" />
        </div>
        <confirmation-modal
            v-if="modalOpen"
            :cancellable="false"
            :button-text="__('OK')"
            @confirm="modalOpen = false"
        >
            <div class="prose">
                <template v-if="addon.installed">
                    <p v-text="`${__('messages.addon_uninstall_command')}:`" />
                    <code-block copyable :text="`composer remove ${package}`" />
                </template>
                <template v-else>
                    <p v-text="`${__('messages.addon_install_command')}:`" />
                    <code-block copyable :text="`composer require ${package}`" />
                </template>
                <p>{{ __('Learn more about') }} <a href="https://statamic.dev/addons">{{ __('Addons') }}</a>.</p>
            </div>
        </confirmation-modal>
        <div>
            <div class="card mb-6 flex items-center">
                <div class="flex-1 text-lg">
                    <div class="little-heading p-0 mb-2 text-gray-700" v-text="__('Price')" />
                    <div class="font-bold" v-text="priceRange" />
                </div>
                <div class="flex-1 text-lg">
                    <div class="little-heading p-0 mb-2 text-gray-700" v-text="__('Seller')" />
                    <a :href="addon.seller.website" class="relative flex items-center">
                        <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full w-6 mr-2">
                        <span class="font-bold">{{ addon.seller.name }}</span>
                    </a>
                </div>
                <div class="flex-1 text-lg" v-if="downloads">
                    <div class="little-heading p-0 mb-2 text-gray-700" v-text="__('Downloads')" />
                    <div class="font-bold">{{ downloads }}</div>
                </div>
            </div>
            <addon-editions v-if="addon.editions.length" :addon="addon" />
            <div class="card content p-8" v-html="description" />
        </div>
    </div>
</template>

<script>
import AddonEditions from './addons/Editions.vue';

    export default {
        components: {
            AddonEditions,
        },

        props: [
            'addon',
        ],

        data() {
            return {
                waitingForRefresh: false,
                modalOpen: false,
                downloads: null,
            }
        },

        computed: {
            toEleven() {
                return {timeout: Statamic.$config.get('ajaxTimeout')};
            },

            package() {
                return this.addon.package;
            },

            description() {
                return this.addon.description;
            },

            priceRange() {
                let [low, high] = this.addon.price_range;
                low = low ? `$${low}` : __('Free');
                high = high ? `$${high}` : __('Free');
                return (low == high) ? low : `${low} - ${high}`;
            },
        },

        created() {
            this.$events.$on('addon-refreshed', this.addonRefreshed);
            this.getDownloadCount();
        },

        destroyed() {
            this.$events.$off('addon-refreshed', this.addonRefreshed);
        },

        methods: {
            addonRefreshed() {
                this.waitingForRefresh = false;
            },

            getDownloadCount() {
                this.$axios.get(`https://packagist.org/packages/${this.addon.package}.json`).then(response => {
                    this.downloads = response.data.package.downloads.total;
                });
            },

            showComposerInstructions() {
                this.modalOpen = true;
            },
        }
    }
</script>
