<template>
    <div class="flex flex-col space-y-3 p-1.5 bg-gray-100 border border-gray-300 dark:bg-gray-900 dark:border-gray-700 rounded-xl">
        <ui-combobox
            :model-value="provider"
            :options="providers"
            option-label="label"
            option-value="value"
            :placeholder="__('Provider...')"
            @update:model-value="changeProvider"
        />

        <ui-input
            v-if="isCloudflare"
            :aria-label="__('Video ID')"
            input-class="border-s-0"
            :isReadOnly="isReadOnly"
            :model-value="videoId"
            :prepend="__('ID')"
            @update:model-value="updateCloudflareId"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <ui-input
            v-else
            :aria-label="__('Video URL')"
            input-class="border-s-0"
            :isReadOnly="isReadOnly"
            :model-value="url"
            :placeholder="__(config.placeholder) || 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'"
            :prepend="__('URL')"
            @update:model-value="updateUrl"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <video v-if="shouldShowPreview && isFile" :src="embedUrl" controls class="w-full rounded-md"></video>

        <div v-else-if="shouldShowPreview" class="video-fieldtype-embed">
            <iframe
                :src="embedUrl"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
            ></iframe>
        </div>

        <ui-description v-else-if="isUnsupported" class="text-red-500">
            {{ __('This video URL is not supported.') }}
        </ui-description>
    </div>
</template>

<script>
import axios from 'axios';
import Fieldtype from './Fieldtype.vue';

const CLOUDFLARE = 'cloudflare';
const FILE = 'file';
const UNSUPPORTED = 'unsupported';
const CLOUDFLARE_PREFIX = 'cloudflare:';

export default {
    mixins: [Fieldtype],

    data() {
        return {
            abortController: null,
            embedUrl: this.meta.video?.embed_url ?? null,
            isVisible: false,
            observer: null,
            provider: this.meta.video?.provider ?? null,
        };
    },

    computed: {
        isCloudflare() {
            return this.provider === CLOUDFLARE || !!this.value?.startsWith(CLOUDFLARE_PREFIX);
        },

        isFile() {
            return this.provider === FILE;
        },

        isUnsupported() {
            return this.provider === UNSUPPORTED && !!this.value;
        },

        providers() {
            return this.meta.providers;
        },

        shouldShowPreview() {
            return this.isVisible && !!this.embedUrl;
        },

        url() {
            return this.value?.startsWith(CLOUDFLARE_PREFIX) ? null : this.value;
        },

        videoId() {
            return this.value?.startsWith(CLOUDFLARE_PREFIX) ? this.value.slice(CLOUDFLARE_PREFIX.length) : null;
        },
    },

    watch: {
        value(value) {
            this.lookup(value);
        },
    },

    methods: {
        changeProvider(provider) {
            this.provider = provider;
            this.embedUrl = null;
            this.update(null);
        },

        lookup(value) {
            if (this.abortController) this.abortController.abort();

            if (!value) {
                this.embedUrl = null;
                return;
            }

            this.abortController = new AbortController();

            this.$axios
                .get(this.meta.url, { params: { value }, signal: this.abortController.signal })
                .then((response) => {
                    if (value !== this.value) return;

                    this.embedUrl = response.data.embed_url;
                    this.provider = response.data.provider;
                })
                .catch((e) => {
                    if (axios.isCancel(e)) return;
                    if (value !== this.value) return;

                    this.embedUrl = null;
                    this.$toast.error(e.response ? e.response.data.message : __('Something went wrong'));
                });
        },

        updateCloudflareId(id) {
            this.updateDebounced(id ? `${CLOUDFLARE_PREFIX}${id}` : null);
        },

        updateUrl(url) {
            this.updateDebounced(url || null);
        },
    },

    mounted() {
        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && entry.intersectionRatio > 0) {
                        this.isVisible = true;
                        this.observer.disconnect();
                    }
                });
            },
            { threshold: 0.01 },
        );

        if (this.$el) {
            this.observer.observe(this.$el);
        }
    },

    beforeUnmount() {
        if (this.observer) {
            this.observer.disconnect();
        }

        if (this.abortController) {
            this.abortController.abort();
        }
    },
};
</script>
