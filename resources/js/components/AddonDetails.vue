<template>
    <div>
        <div class="flex items-center mb-3">
            <h1 class="flex-1" v-text="addon.name" />
            <button v-if="addon.installed" class="btn" :disabled="processing" @click="uninstall">Uninstall Addon</button>
            <button v-else class="btn btn-primary" :disabled="processing" @click="install">Install Addon</button>
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
                    <div class="little-heading p-0 mb-1 text-grey-70">Price</div>
                    <div class="font-bold" v-text="priceRange" />
                </div>
                <div class="flex-1 text-lg">
                    <div class="little-heading p-0 mb-1 text-grey-70">Seller</div>
                    <a :href="addon.seller.website" class="relative flex items-center">
                        <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full w-6 mr-1">
                        <span class="font-bold">{{ addon.seller.name }}</span>
                    </a>
                </div>
                <div class="flex-1 text-lg">
                    <div class="little-heading p-0 mb-1 text-grey-70">Downloads</div>
                    <div class="font-bold">1,234</div>
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
                showComposer: false
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
                return markdown(this.addon.description);
            },

            priceRange() {
                let [low, high] = this.addon.price_range;
                low = low ? `$${low}` : 'Free';
                high = high ? `$${high}` : 'Free';
                return (low == high) ? low : `${low} - ${high}`;
            },
        },

        created() {
            this.$events.$on('composer-finished', this.composerFinished);
            this.$events.$on('addon-refreshed', this.addonRefreshed);
            this.$store.commit('statamic/composer', {});
        },

        methods: {
            install() {
                this.$axios.post(cp_url('addons/install'), {'addon': this.package}, this.toEleven);

                this.waitingForRefresh = true;
                this.showComposer = true;

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Installing ' + this.package,
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
                    status: 'Uninstalling ' + this.package,
                    package: this.package,
                });

                setTimeout(() => this.$events.$emit('start-composer'), 100);
            },

            composerFinished() {
                this.$store.commit('statamic/composer', {
                    processing: false,
                    status: 'Operation complete!',
                    package: this.package,
                });
            },

            addonRefreshed() {
                this.waitingForRefresh = false;
            },
        }
    }
</script>
