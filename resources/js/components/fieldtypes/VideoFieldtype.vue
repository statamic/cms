<template>
    <div class="flex flex-col space-y-3 p-1.5 bg-gray-100 border border-gray-300 dark:bg-gray-900 dark:border-gray-700 rounded-xl">
        <ui-input-group>
            <ui-input-group-prepend :text="__('URL')" />
            <ui-input
                :model-value="value"
                :isReadOnly="isReadOnly"
                :placeholder="__(config.placeholder) || 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'"
                @update:model-value="update"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
            />
        </ui-input-group>
        <ui-description v-if="isInvalid" class="text-red-500">{{ __('statamic::validation.url') }}</ui-description>
        <iframe
            v-if="shouldShowPreview"
            :src="embedUrl"
            frameborder="0"
            allow="fullscreen"
            class="aspect-video rounded-lg"
        ></iframe>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

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
    },
};
</script>
