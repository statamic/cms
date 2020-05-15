<template>
    <div>
        <img :src="cover" class="rounded-t">
        <div class="flex items-center justify-between px-4 py-2 mb-2 border-b">
            <a :href="addon.seller.website" class="relative flex items-center">
                <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full h-14 w-14 mr-2">
                <span class="font-bold">{{ addon.seller.name }}</span>
            </a>
            <button v-if="addon.installed" class="btn" :disabled="processing" @click="uninstall">Uninstall Addon</button>
            <button v-else class="btn" :disabled="processing" @click="install">Install Addon</button>
        </div>
        <composer-output v-show="composer.status" :package="package" class="m-3"></composer-output>
        <div v-if="! composer.status" class="p-4 content" v-html="description" />
    </div>
</template>

<script>
    export default {
        props: [
            'addon',
            'cover',
        ],

        data() {
            return {
                waitingForRefresh: false,
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
            }
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

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Installing ' + this.package,
                    package: this.package,
                });

                this.$events.$emit('start-composer');
            },

            uninstall() {
                this.$axios.post(cp_url('addons/uninstall'), {'addon': this.package}, this.toEleven);

                this.waitingForRefresh = true;

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Uninstalling ' + this.package,
                    package: this.package,
                });

                this.$events.$emit('start-composer');
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
