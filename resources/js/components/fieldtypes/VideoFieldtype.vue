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
                    placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')" />
            </div>
        </div>

        <div class="video-preview-wrapper" v-if="isEmbeddable || isVideo">
            <div class="video-preview">
                <iframe v-if="isEmbeddable" width="560" height="315" :src="embed" frameborder="0"></iframe>
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
            data: this.value || ''
        }
    },

    watch: {

        data(value) {
            this.update(value);
        },

        value(value) {
            this.data = value;
        }

    },

    computed: {
        embed() {
            if (this.data.includes('youtube')) {
                return this.data.replace('watch?v=', 'embed/');
            }

            if (this.data.includes('youtu.be')) {
                return this.data.replace('youtu.be', 'www.youtube.com/embed');
            }

            if (this.data.includes('vimeo')) {
                return this.data.replace('/vimeo.com', '/player.vimeo.com/video');
            }

            return this.data;
        },

        isEmbeddable() {
            return this.isUrl && this.data.includes('youtube') || this.data.includes('vimeo') || this.data.includes('youtu.be');
        },

        isUrl() {
            let regex = new RegExp('^(https?|ftp)://[^\s/$.?#].[^\s]*$', 'i')

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
