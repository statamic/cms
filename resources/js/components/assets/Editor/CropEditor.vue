<template>
    <portal name="crop-editor">
        <div class="crop-editor fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <Card class="crop-editor-toolbox max-w-4xl" inset>
                <div class="p-6">
                    <Heading size="xl">{{ __('Crop Image') }}</Heading>
                    <Subheading>{{ __('Adjust the crop area and click Finish to apply') }}</Subheading>
                    <div class="crop-editor-image mt-4">
                        <img ref="image" :src="image" class="max-w-full" />
                    </div>
                </div>
                <div class="px-4 pb-4">
                    <div class="mb-4 flex flex-wrap items-center justify-center gap-2">
                        <Button :text="__('Cancel')" @click="close" />
                        <Button :text="__('Reset')" @click="reset" />
                        <Button variant="primary" :text="__('Finish')" @click="crop" />
                    </div>
                </div>
            </Card>
        </div>
    </portal>
</template>

<script>
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import {
    Card,
    Heading,
    Subheading,
    Button,
} from '@ui';

export default {
    emits: ['cropped', 'closed'],

    components: {
        Card,
        Heading,
        Subheading,
        Button,
    },

    props: {
        image: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            cropper: null,
        };
    },

    async mounted() {
        await this.$nextTick();
        this.initCropper();
    },

    beforeUnmount() {
        if (this.cropper) {
            this.cropper.destroy();
        }
    },

    methods: {
        initCropper() {
            const imageElement = this.$refs.image;
            if (!imageElement) return;

            this.cropper = new Cropper(imageElement, {
                aspectRatio: NaN,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleable: false,
                zoomable: true,
                scalable: false,
                rotatable: false,
                responsive: true,
            });
        },

        crop() {
            if (!this.cropper) return;

            const canvas = this.cropper.getCroppedCanvas({
                width: this.cropper.getImageData().naturalWidth,
                height: this.cropper.getImageData().naturalHeight,
            });

            if (!canvas) {
                this.$toast.error(__('Failed to crop image'));
                return;
            }

            canvas.toBlob((blob) => {
                if (!blob) {
                    this.$toast.error(__('Failed to create cropped image'));
                    return;
                }

                this.$emit('cropped', blob);
                this.close();
            }, 'image/jpeg', 0.95);
        },

        reset() {
            if (this.cropper) {
                this.cropper.reset();
            }
        },

        close() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
            this.$emit('closed');
        },
    },
};
</script>

<style scoped>
.crop-editor-image {
    max-height: 70vh;
    overflow: auto;
}

.crop-editor-image img {
    display: block;
    max-width: 100%;
}
</style>
