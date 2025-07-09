<template>
    <div class="flex flex-col space-y-3 rounded-xl border border-gray-300 bg-gray-100 p-1.5 dark:border-gray-700 dark:bg-gray-900">
        <Combobox
            v-model="provider"
            :options="providers"
            option-label="provider"
            option-value="provider"
            :placeholder="__('Provider...')"
        />
        <Input
            v-if="provider != 'Cloudflare'"
            :model-value="url"
            :isReadOnly="isReadOnly"
            :placeholder="__(config.placeholder) || 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'"
            :prepend="__('URL')"
            @update:model-value="detailsFromUrl"
        />
        <Input
            v-if="provider == 'Cloudflare'"
            :model-value="videoId"
            :isReadOnly="isReadOnly"
            :prepend="__('ID')"
            @update:model-value="detailsFromCloudflare"
        />
        <div
            v-if="embedUrl"
            class="aspect-video rounded-lg"
            v-html="embedUrl"
        ></div>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Combobox, Input } from '@statamic/ui';

export default {
    components: { Combobox, Input },

    mixins: [Fieldtype],

    data() {
        return {
            embedUrl: null,
            provider: null,
            savedValue: null,
            url: null,
            videoId: null,
        };
    },

    computed: {
        providers() {
            return this.meta.providers;
        },
    },

    methods: {
        detailsFromCloudflare(id) {
            if (id == null) return;

            this.savedValue = `cloudflare::${id}`;
            this.videoId = id;
            this.url = null;

            this.getVideoData({type: this.provider, id: this.videoId});
        },

        detailsFromUrl(url) {
            if (url == null) return;

            this.savedValue = url;
            this.videoId = null;
            this.url = url;

            this.getVideoData({url: url});
        },

        getVideoData(params) {
            this.debounce(() => {
                this.$axios
                    .get(this.meta.url, { params: params })
                    .then((response) => response.data)
                    .then((data) => {
                        this.embedUrl = data.embed_url;
                        this.provider = data.provider;
                    });

                this.update(this.savedValue);
            })
        },

    }
};
</script>
