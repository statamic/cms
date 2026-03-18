<template>
    <div class="flex flex-col space-y-3 p-1.5 bg-gray-100 border border-gray-300 dark:bg-gray-900 dark:border-gray-700 rounded-xl">
<!--
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
            v-else
            :model-value="videoId"
            :isReadOnly="isReadOnly"
            :prepend="__('ID')"
            @update:model-value="detailsFromCloudflare"
        />
-->
        <ui-input-group>
            <ui-input-group-prepend :text="__('URL')" />
            <ui-input
                :model-value="value"
                :isReadOnly="isReadOnly"
                :placeholder="__(config.placeholder) || 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'"
                :aria-label="__('Video URL')"
                @update:model-value="update"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
                input-class="border-s-0"
            />
        </ui-input-group>
        <ui-description v-if="isInvalid" class="text-red-600">{{ __('statamic::validation.url') }}</ui-description>
        <iframe
            v-if="shouldShowPreview"
            ref="iframe"
            :src="isVisible ? embedUrl : null"
            frameborder="0"
            allow="fullscreen"
            class="rounded-lg aspect-video"
            loading="lazy"
        ></iframe>
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
            isVisible: false,
            observer: null,
        };
    },

    computed: {
        shouldShowPreview() {
            return !this.isInvalid && (this.isEmbeddable || this.isVideo);
        },

        embedUrl() {
            let embed_url = this.value || '';

            if (embed_url.includes('youtube')) {
                embed_url = embed_url.includes('shorts/')
                    ? embed_url.replace('shorts/', 'embed/')
                    : embed_url.replace('watch?v=', 'embed/');
            }

            if (embed_url.includes('youtu.be')) {
                embed_url = embed_url.replace('youtu.be', 'www.youtube.com/embed');
            }

            if (embed_url.includes('vimeo')) {
                embed_url = embed_url.replace('/vimeo.com', '/player.vimeo.com/video');

                if (!this.value.includes('progressive_redirect') && embed_url.split('/').length > 5) {
                    let hash = embed_url.substr(embed_url.lastIndexOf('/') + 1);
                    embed_url = embed_url.substr(0, embed_url.lastIndexOf('/')) + '?h=' + hash.replace('?', '&');
                }
            }

            if (embed_url.includes('&') && !embed_url.includes('?')) {
                embed_url = embed_url.replace('&', '?');
            }

            return embed_url;
        },

        isEmbeddable() {
            const url = this.value || '';
            const isYoutube = url.includes('youtube') || url.includes('youtu.be');
            const isVimeo = url.includes('vimeo');
            return isYoutube || isVimeo;
        },

        isInvalid() {
            let htmlRegex = new RegExp(/<([A-Z][A-Z0-9]*)\b[^>]*>.*?<\/\1>|<([A-Z][A-Z0-9]*)\b[^\/]*\/>/i);
            return htmlRegex.test(this.value || '');
        },

        isUrl() {
            const url = this.value || '';
            return url.startsWith('http://') || url.startsWith('https://');
        },

        isVideo() {
            const url = this.value || '';
            const isVideo = url.includes('.mp4') || url.includes('.ogv') || url.includes('.mov') || url.includes('.webm');
            return !this.isEmbeddable && isVideo;
        },

        providers() {
            return this.meta.providers;
        }
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
            this.$axios
                .get(this.meta.url, { params: params })
                .then((response) => response.data)
                .then((data) => {
                    this.embedUrl = data.embed_url;
                    this.provider = data.provider;
                });

            this.update(this.savedValue);
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
    },
};
</script>
