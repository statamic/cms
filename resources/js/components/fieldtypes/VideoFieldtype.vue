<template>
    <div class="flex flex-col space-y-3 p-1.5 bg-gray-100 border border-gray-300 dark:bg-gray-900 dark:border-gray-700 rounded-xl">
        <ui-combobox
            v-model="provider"
            :options="providers"
            option-label="provider"
            option-value="provider"
            :placeholder="__('Provider...')"
        />

        <ui-input
            v-if="provider != 'Cloudflare'"
            :aria-label="__('Video URL')"
            input-class="border-s-0"
            :isReadOnly="isReadOnly"
            :model-value="url"
            :placeholder="__(config.placeholder) || 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'"
            :prepend="__('URL')"
            @update:model-value="detailsFromUrl"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <ui-input
            v-else
            :aria-label="__('Video ID')"
            input-class="border-s-0"
            :isReadOnly="isReadOnly"
            :model-value="videoId"
            :prepend="__('ID')"
            @update:model-value="detailsFromCloudflare"
        />

        <div v-if="shouldShowPreview" v-html="embed"></div>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

    data() {
        return {
            embed: this.meta.embed,
            isVisible: false,
            observer: null,
            provider: this.meta.provider,
            savedValue: null,
            url: null,
            videoId: null,
        };
    },

    computed: {
        shouldShowPreview() {
            return this.embed;
        },

        providers() {
            return this.meta.providers;
        }
    },

    watch: {
        provider(newProvider, oldProvider) {
            if (newProvider != oldProvider) {
                this.embed = null;
                this.url = null;
            }
        }
    },

    methods: {
        detailsFromCloudflare(id) {
            if (id == null) return;

            this.savedValue = `cloudflare:${id}`;
            this.videoId = id;
            this.url = null;

            this.getVideoData();
        },

        detailsFromUrl(url) {
            if (url == null) return;

            this.savedValue = url;
            this.videoId = null;
            this.url = url;

            this.getVideoData();
        },

        getVideoData() {
            this.$axios
                .get(this.meta.url, { params: { url: this.savedValue } })
                .then((response) => response.data)
                .then((data) => {
                    this.embed = data.embed;
                    this.provider = data.provider;
                });

            this.update(this.savedValue);
        },

        setUrlOrId() {
            if (this.value?.startsWith('cloudflare:')) {
                this.videoId = this.value.replace('cloudflare:','');
                return;
            }

            this.url = this.value;
        }
    },

    mounted() {
        this.setUrlOrId();
        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && entry.intersectionRatio > 0) {
                        this.isVisible = true;
                        this.observer.disconnect();
                    }
                });
            },
            { threshold: 0.01 }
        );

        if (this.$el) {
            this.observer.observe(this.$el);
        }
    },

    beforeUnmount() {
        if (this.observer) {
            this.observer.disconnect();
        }
    },
};
</script>
