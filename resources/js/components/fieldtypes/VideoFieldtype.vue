<template>
    <input type="text" v-model="data" class="form-control" />

    <div class="video-preview-wrapper" v-if="isEmbeddable || isVideo">
        <div class="video-preview">
            <iframe v-if="isEmbeddable" width="560" height="315" src="{{ embed }}" frameborder="0" allowfullscreen></iframe>
            <video controls v-if="isVideo" :src="embed" width="560" height="315"></video>
        </div>
    </div>
</template>

<script>
export default {
    props: ['data', 'config', 'name'],

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
            return this.data.includes('youtube') || this.data.includes('vimeo') || this.data.includes('youtu.be');
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

<style>
    .video-fieldtype .video-preview-wrapper {
        padding: 16px;
        background: #f6f9fc;
		border-top: 1px solid #e0e0e0;
		border-bottom-left-radius: 3px;
        border-bottom-right-radius: 3px;
    }
    .video-fieldtype .video-preview {
        position: relative;
        padding: 25px 0 56.25%;
        height: 0;
    }

    .video-fieldtype .video-preview iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .video-fieldtype .video-preview video {
        width: 100% !important;
        height: auto !important;
    }
</style>
