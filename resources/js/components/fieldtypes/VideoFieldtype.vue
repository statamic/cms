<template>
    <div class="flex flex-col space-y-3 p-1.5 bg-gray-100 border border-gray-300 dark:bg-gray-900 dark:border-gray-700 rounded-xl">
        <ui-combobox
            :model-value="mode"
            :options="meta.providers"
            option-label="label"
            option-value="value"
            :aria-label="__('Video Provider')"
            @update:model-value="changeMode"
        />
        <ui-input-group v-if="isCloudflare">
            <ui-input-group-prepend :text="__('ID')" />
            <ui-input
                :model-value="videoId"
                :isReadOnly="isReadOnly"
                :aria-label="__('Video ID')"
                @update:model-value="updateCloudflareId"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
                input-class="border-s-0"
            />
        </ui-input-group>
        <ui-input-group v-else>
            <ui-input-group-prepend :text="__('URL')" />
            <ui-input
                :model-value="value"
                :isReadOnly="isReadOnly"
                :placeholder="__(config.placeholder) || 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'"
                :aria-label="__('Video URL')"
                @update:model-value="updateDebounced"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
                input-class="border-s-0"
            />
        </ui-input-group>
        <ui-description v-if="isInvalid" class="text-red-600">{{ __('statamic::validation.url') }}</ui-description>
        <video
            v-if="shouldShowPreview && isFile"
            :src="isVisible ? embedUrl : null"
            controls
            class="aspect-video rounded-lg w-full"
        ></video>
        <iframe
            v-else-if="shouldShowPreview"
            ref="iframe"
            :src="isVisible ? embedUrl : null"
            frameborder="0"
            allow="fullscreen"
            class="aspect-video rounded-lg"
            loading="lazy"
        ></iframe>
    </div>
</template>

<script>
import axios from 'axios';
import Fieldtype from './Fieldtype.vue';

const CLOUDFLARE = 'cloudflare';
const CLOUDFLARE_PREFIX = 'cloudflare:';
const FILE = 'file';
const UNSUPPORTED = 'unsupported';
const URL_MODE = 'url';

export default {
    mixins: [Fieldtype],

    data() {
        return {
            abortController: null,
            isVisible: false,
            lookedUp: this.meta.video,
            observer: null,
            // Only consulted when there's no value; otherwise the value itself says which input to show.
            mode: this.meta.video?.provider === CLOUDFLARE ? CLOUDFLARE : URL_MODE,
        };
    },

    computed: {
        shouldShowPreview() {
            return !this.isInvalid && !!this.embedUrl;
        },

        isFile() {
            return this.lookedUp?.url === this.value && this.lookedUp.provider === FILE;
        },

        embedUrl() {
            if (this.isCloudflare) {
                return this.videoId ? `https://iframe.cloudflarestream.com/${this.videoId}` : null;
            }

            return this.lookedUp?.url === this.value ? this.lookedUp.embed_url : null;
        },

        isCloudflare() {
            return this.value?.startsWith(CLOUDFLARE_PREFIX) || (!this.value && this.mode === CLOUDFLARE);
        },

        isInvalid() {
            if (this.isCloudflare) return !!this.videoId && !/^[a-zA-Z0-9]+$/.test(this.videoId);

            return !!this.value && this.lookedUp?.url === this.value && this.lookedUp.provider === UNSUPPORTED;
        },

        videoId() {
            return this.value?.startsWith(CLOUDFLARE_PREFIX) ? this.value.slice(CLOUDFLARE_PREFIX.length) : null;
        },
    },

    watch: {
        value: {
            handler(value) {
                if (value?.startsWith(CLOUDFLARE_PREFIX) || !value) return;

                this.lookup(value);
            },
            immediate: true,
        },
    },

    methods: {
        changeMode(mode) {
            if (mode === this.mode) return;

            this.mode = mode;

            if (this.value) this.update(null);
        },

        lookup(value) {
            if (this.lookedUp?.url === value) return;

            if (this.abortController) this.abortController.abort();

            this.abortController = new AbortController();

            this.$axios
                .get(this.meta.detailsUrl, { params: { value }, signal: this.abortController.signal })
                .then((response) => {
                    // Ignore a response that arrived after the value moved on.
                    if (value === this.value) this.lookedUp = response.data;
                })
                .catch((e) => {
                    if (axios.isCancel(e)) return;
                    if (value !== this.value) return;

                    this.lookedUp = null;
                    this.$toast.error(e.response ? e.response.data.message : __('Something went wrong'));
                });
        },

        updateCloudflareId(id) {
            this.update(id ? `${CLOUDFLARE_PREFIX}${id}` : null);
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

        if (this.abortController) {
            this.abortController.abort();
        }
    },
};
</script>
