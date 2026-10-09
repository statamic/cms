<template>
    <div class="flex flex-col space-y-3 p-1.5 bg-gray-100 border border-gray-300 dark:bg-gray-900 dark:border-gray-700 rounded-xl">
        <ui-input-group>
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
        <iframe
            v-if="shouldShowPreview"
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
import Fieldtype from './Fieldtype.vue';

const CLOUDFLARE_URL_PATTERN = /^https?:\/\/(customer-[a-z0-9]+\.cloudflarestream\.com)\/([a-z0-9]+)(?:[/?#]|$)/i;

export default {
    mixins: [Fieldtype],

    data() {
        return {
            isVisible: false,
            observer: null,
        };
    },

    computed: {
        shouldShowPreview() {
            return !this.isInvalid && (this.isEmbeddable || this.isVideo);
        },

        cloudflare() {
            const match = CLOUDFLARE_URL_PATTERN.exec(this.value || '');

            return match ? { host: match[1], id: match[2] } : null;
        },

        embedUrl() {
            if (this.cloudflare) return `https://${this.cloudflare.host}/${this.cloudflare.id}/iframe`;

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
            return isYoutube || isVimeo || !!this.cloudflare;
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
