<template>
    <div
        class="flex flex-col space-y-3 rounded-xl border border-gray-300 bg-gray-100 p-1.5 dark:border-gray-700 dark:bg-gray-900"
    >
        <Select
            :options="providers"
            option-label="name"
            option-value="handle"
            :placeholder="__('Provider...')"
            v-model="provider"
        />
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
import { Select } from '@statamic/ui';

export default {
    components: { Select },

    mixins: [Fieldtype],

    data() {
        return {
            embedUrl: null,
            provider: null,
        };
    },

    computed: {
        providers() {
            return this.meta.providers;
        },

        shouldShowPreview() {
            return !this.isInvalid && (this.isEmbeddable || this.isVideo);
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
            const isVideo =
                url.includes('.mp4') || url.includes('.ogv') || url.includes('.mov') || url.includes('.webm');
            return !this.isEmbeddable && isVideo;
        },
    },

    watch: {
        value() {
            this.$axios
                .get(this.meta.url, { params: { url: this.value } })
                .then((response) => response.data)
                .then((data) => {
                    this.provider = data.provider;
                    this.embedUrl = data.embed_url;
                });
        },
    },
};
</script>
