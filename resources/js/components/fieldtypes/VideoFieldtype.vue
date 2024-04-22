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
                    :placeholder="__(config.placeholder) || 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')" />
            </div>
        </div>

        <p v-if="isInvalid" class="text-red-500 mt-4">{{ __('statamic::validation.url') }}</p>

        <div class="video-preview-wrapper" v-if="!isInvalid && (isEmbeddable || isVideo)">
            <div class="embed-video" v-if="isEmbeddable && canShowIframe">
                <iframe :src="embedUrl" frameborder="0" allow="fullscreen"></iframe>
            </div>
            <div class="native-video" v-else-if="isVideo">
                <video controls :src="embedUrl"></video>
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
        embedUrl() {
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
                if (embed_url.split('/').length > 5) {
                    let hash = embed_url.substr(embed_url.lastIndexOf('/') + 1);
                    embed_url = embed_url.substr(0, embed_url.lastIndexOf('/')) + '?h=' + hash.replace('?', '&');
                }
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

        isInvalid() {
            let htmlRegex = new RegExp(/<([A-Z][A-Z0-9]*)\b[^>]*>.*?<\/\1>|<([A-Z][A-Z0-9]*)\b[^\/]*\/>/i)

            return htmlRegex.test(this.data);
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
