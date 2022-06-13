<template>
    <div>
        <div class="flex items-center mb-3">
            <h1 class="flex-1" v-text="addon.name" />
            <a :href="addon.url" target="_blank" class="btn mr-2" v-text="__('View on Marketplace')" />
            <button v-if="addon.installed" class="btn" :disabled="processing" @click="uninstall" v-text="__('Uninstall')" />
            <button v-else class="btn btn-primary" :disabled="processing" @click="install" v-text="__('Install')" />
        </div>
        <modal
            name="addon-composer-output"
            v-if="showComposer"
            v-slot="{ close: closeModal }"
            :close-on-click="!composer.processing"
            :pivot-y="0.5"
            :overflow="false"
            width="75%"
            @closed="showComposer = false"
        >
            <div class="p-3 relative">
                <composer-output :package="package" />
                <button
                    v-if="!composer.processing"
                    class="btn-close absolute top-0 right-0 mt-2 mr-2"
                    aria-label="Close"
                    @click="closeModal"
                    v-html="'&times'" />
            </div>
        </modal>
        <div>
            <div class="card mb-3 flex items-center">
                <div class="flex-1 text-lg">
                    <div class="little-heading p-0 mb-1 text-grey-70" v-text="__('Price')" />
                    <div class="font-bold" v-text="priceRange" />
                </div>
                <div class="flex-1 text-lg">
                    <div class="little-heading p-0 mb-1 text-grey-70" v-text="__('Seller')" />
                    <a :href="addon.seller.website" class="relative flex items-center">
                        <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full w-6 mr-1">
                        <span class="font-bold">{{ addon.seller.name }}</span>
                    </a>
                </div>
                <div class="flex-1 text-lg" v-if="downloads">
                    <div class="little-heading p-0 mb-1 text-grey-70" v-text="__('Downloads')" />
                    <div class="font-bold">{{ downloads }}</div>
                </div>
            </div>
            <addon-editions v-if="addon.editions.length" :addon="addon" />
            <div class="card content p-4" v-html="description" />
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
                showComposer: false,
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

            composer() {
                return this.$store.state.statamic.composer;
            },

            processing() {
                return this.composer.processing || this.waitingForRefresh;
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
            this.$events.$on('composer-finished', this.composerFinished);
            this.$events.$on('addon-refreshed', this.addonRefreshed);
            this.$store.commit('statamic/composer', {});
            this.getDownloadCount();
        },

        destroyed() {
            this.$events.$off('composer-finished', this.composerFinished);
            this.$events.$off('addon-refreshed', this.addonRefreshed);
        },

        methods: {
            install() {
                this.$axios.post(cp_url('addons/install'), {'addon': this.package}, this.toEleven);

                this.waitingForRefresh = true;
                this.showComposer = true;

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: __('Installing :package', { package: this.package }),
                    package: this.package,
                });

                setTimeout(() => this.$events.$emit('start-composer'), 100);
            },

            uninstall() {
                this.$axios.post(cp_url('addons/uninstall'), {'addon': this.package}, this.toEleven);

                this.waitingForRefresh = true;
                this.showComposer = true;

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: __('Uninstalling :package', { package: this.package }),
                    package: this.package,
                });

                setTimeout(() => this.$events.$emit('start-composer'), 100);
            },

            composerFinished() {
                this.$store.commit('statamic/composer', {
                    processing: false,
                    status: __('Operation complete'),
                    package: this.package,
                });

                this.$toast.success(__('Operation complete'));
            },

            addonRefreshed() {
                this.waitingForRefresh = false;
            },

            getDownloadCount() {
                this.$axios.get(`https://packagist.org/packages/${this.addon.package}.json`).then(response => {
                    this.downloads = response.data.package.downloads.total;
                });
            }
        }
    }
</script>
