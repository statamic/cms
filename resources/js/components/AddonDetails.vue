<template>
    <div>
        <div class="flex items-center mb-6">
            <h1 class="flex-1" v-text="addon.name" />
            <a :href="addon.url" target="_blank" class="btn">
                <svg-icon name="light/external-link" class="w-3 h-3 rtl:ml-2 ltr:mr-2 shrink-0" />
                {{ __('View on Marketplace') }}
            </a>
        </div>
        <div class="flex flex-col-reverse xl:grid xl:grid-cols-3 space-y-6 xl:space-y-0 gap-6">
            <div class="lg:col-span-2">
                <div class="card prose max-w-full p-6" v-html="description" />
            </div>
            <div class="xl:col-span-1 flex flex-col space-y-6">
                <div class="card flex flex-col space-y-6 p-6">
                    <div class="flex-1 text-lg">
                        <div class="little-heading p-0 mb-2 text-gray-700" v-text="__('Seller')" />
                        <a :href="addon.seller.website" target="_blank" class="relative flex items-center">
                            <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full w-6 rtl:ml-2 ltr:mr-2">
                            <span class="font-bold">{{ addon.seller.name }}</span>
                        </a>
                    </div>
                    <div class="flex-1 text-lg">
                        <div class="little-heading p-0 mb-2 text-gray-700" v-text="__('Price')" />
                        <div class="font-bold" v-text="priceRange" />
                    </div>
                    <div class="flex-1 text-lg" v-if="downloads">
                        <div class="little-heading p-0 mb-2 text-gray-700" v-text="__('Downloads')" />
                        <div class="font-bold">{{ downloads }}</div>
                    </div>
                </div>
                <div class="card p-6">
                    <div class="prose">
                        <template v-if="addon.installed">
                            <p class="leading-snug" v-text="`${__('messages.addon_uninstall_command')}:`" />
                            <code-block class="text-xs" copyable :text="`composer remove ${package}`" />
                        </template>
                        <template v-else>
                            <p v-text="`${__('messages.addon_install_command')}:`" />
                            <code-block copyable :text="installCommand" />
                        </template>
                        <p v-html="link"></p>
                    </div>
                </div>
                <addon-editions v-if="addon.editions.length" :addon="addon" />
            </div>
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

            link() {
                return __('Learn more about :link', { link: `<a href="https://statamic.dev/addons" target="_blank">${__('Addons')}</a>`}) + '.';
            },

            installCommand() {
                switch (this.package) {
                    case 'statamic/collaboration':
                        return 'php please install:collaboration';
                    case 'statamic/eloquent-driver':
                        return 'php please install:eloquent-driver';
                    case 'statamic/ssg':
                        return 'php please install:ssg';
                    default:
                        return `composer require ${this.package}`;
                }
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
        }
    }
</script>
