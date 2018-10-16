<template>
    <div>
        <img :src="cover" class="rounded-t">
        <div class="flex items-center justify-between px-4 py-2 mb-2 border-b">
            <a :href="addon.seller.website" class="relative flex items-center">
                <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full h-14 w-14 mr-2">
                <span class="font-bold">{{ addon.seller.name }}</span>
            </a>
            <button class="btn" @click="install">Install Addon</button>
            <button class="btn" @click="uninstall">Uninstall Addon</button>
        </div>
        <composer-output v-show="composer.status" class="m-3"></composer-output>
        <div v-if="! composer.status" class="p-4">{{ addon.variants[0].description }}</div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        props: [
            'addon',
            'cover',
        ],

        data() {
            return {
                //
            }
        },

        computed: {
            toEleven() {
                return {timeout: window.Statamic.ajaxTimeout};
            },

            package() {
                return this.addon.variants[0].githubRepo;
            },

            composer() {
                return this.$store.state.statamic.composer;
            },
        },

        created() {
            //
        },

        methods: {
            install() {
                axios.post('/cp/addons/install', {'addon': this.package}, this.toEleven);

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Installing ' + this.package,
                    package: this.package,
                });

                this.$events.$emit('start-composer');
            },

            uninstall() {
                axios.post('/cp/addons/uninstall', {'addon': this.package}, this.toEleven);

                this.$store.commit('statamic/composer', {
                    processing: true,
                    status: 'Uninstalling ' + this.package,
                    package: this.package,
                });

                this.$events.$emit('start-composer');
            },
        }
    }
</script>
