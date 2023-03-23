<template>
    <div class="video-fieldtype-container">
        <div class="flex items-center">
            <div class="input-group">
                <div class="input-group-prepend">{{ __('URL') }}</div>
                <input type="text"
                    v-model="data"
                    class="input-text flex-1"
                    :class="{ 'bg-white': !isReadOnly }"
                    :id="fieldId"
                    :readonly="isReadOnly"
                    :placeholder="config.placeholder || 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')" />
            </div>
        </div>

        <div class="video-preview-wrapper" v-if="isEmbeddable || isVideo">
            <div class="video-preview">
                <iframe v-if="isEmbeddable && canShowIframe" width="560" height="315" :src="embed" frameborder="0" allow="fullscreen"></iframe>
                <video controls v-if="isVideo" :src="embed" width="560" height="315"></video>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    mixins: [Fieldtype],

    data() {
        return {
            data: this.value || '',
            canShowIframe: false,
        }
    },

    watch: {

        data: _.debounce(function (value)  {
            this.update(value);
        }, 500),

        value(value) {
            this.data = value;
        }

    },

    mounted() {
        // Showing the iframe right away causes Vue to stop in Safari.
        this.canShowIframe = true;
    },

    computed: {
        embed() {
            let embed_url = this.data;

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
            }

            // Make sure additional query parameters are included.
            if (embed_url.includes('&') && !embed_url.includes('?')) {
                embed_url = embed_url.replace('&', '?');
            }

            return embed_url;
        },

        isEmbeddable() {
            return this.isUrl && this.data.includes('youtube') || this.data.includes('vimeo') || this.data.includes('youtu.be');
        },

        isUrl() {
            let regex = new RegExp('^(https?|ftp):\/\/[^\s/$.?#].*$', 'i')

            return regex.test(this.data);
        },

        isVideo() {
            return ! this.isEmbeddable && (
                this.data.includes('.mp4') ||
                this.data.includes('.ogv') ||
                this.data.includes('.mov') ||
                this.data.includes('.webm')
            )
        }
    },
};
</script>
