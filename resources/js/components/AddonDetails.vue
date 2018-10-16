<template>
    <div>
        <img :src="cover" class="rounded-t">
        <div class="flex items-center justify-between px-4 py-2 mb-2 border-b">
            <a :href="addon.seller.website" class="relative flex items-center">
                <img :src="addon.seller.avatar" :alt="addon.seller.name" class="rounded-full h-14 w-14 mr-2">
                <span class="font-bold">{{ addon.seller.name }}</span>
            </a>
            <button class="btn" @click="install">
                Install Addon
            </button>
        </div>
        <composer-output v-show="output" :title="output.status" class="m-3"></composer-output>
        <div v-if="! output" class="p-4">{{ addon.variants[0].description }}</div>
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
                output: false,
            }
        },

        created() {
            this.rows = this.getAddons()
        },

        methods: {
            install() {
                var repo = addon.variants[0].githubRepo;

                axios.post('/cp/addons/install', {'addon': repo}, this.toEleven);

                this.output = {
                    processing: true,
                    status: 'Installing ' + repo,
                };

                this.$events.$emit('start-composer');
            }
        }
    }
</script>
