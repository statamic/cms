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
                    <div class="mb-4">
                        <div class="mb-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ __('Aspect Ratio') }}</div>
                        <div class="flex items-center justify-center gap-2">
                            <Select
                                v-model="selectedRatio"
                                :options="aspectRatios"
                                option-label="label"
                                option-value="value"
                                :placeholder="__('Aspect ratio')"
                                size="sm"
                                class="w-48"
                                @update:modelValue="setAspectRatio"
                            />
                            <Button
                                v-if="selectedRatio !== null"
                                icon="flip-vertical"
                                :variant="isFlipped ? 'pressed' : 'ghost'"
                                size="sm"
                                :text="__('Flip Orientation')"
                                @click="toggleOrientation"
                            />
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-2">
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
    Select,
} from '@ui';

export default {
    emits: ['cropped', 'closed'],

    components: {
        Card,
        Heading,
        Subheading,
        Button,
        Select,
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
            selectedRatio: null,
            baseRatio: null,
            isFlipped: false,
            aspectRatios: [
                { label: '16:9', value: 16 / 9 },
                { label: '4:3', value: 4 / 3 },
                { label: '3:2', value: 3 / 2 },
                { label: '2:1', value: 2 / 1 },
                { label: '1:1', value: 1 },
            ],
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

        setAspectRatio(ratio) {
            if (!this.cropper) return;

            if (ratio === null) {
                this.cropper.setAspectRatio(NaN);
                this.baseRatio = null;
                this.isFlipped = false;
            } else {
                this.baseRatio = ratio;
                this.isFlipped = false;
                this.applyCurrentRatio();
            }
        },

        toggleOrientation() {
            if (!this.cropper || this.baseRatio === null) return;

            // Toggle the flipped state
            this.isFlipped = !this.isFlipped;
            this.applyCurrentRatio();
        },

        applyCurrentRatio() {
            if (!this.cropper || this.baseRatio === null) return;

            const ratioToApply = this.isFlipped ? 1 / this.baseRatio : this.baseRatio;

            // Find if the ratio to apply matches one in our list
            const matchingRatio = this.aspectRatios.find(r => Math.abs(r.value - ratioToApply) < 0.001);

            if (matchingRatio && matchingRatio.value === ratioToApply) {
                // If the ratio is in our list, update the select to show it
                this.selectedRatio = matchingRatio.value;
            }

            // Apply the ratio to the cropper
            this.cropper.setAspectRatio(ratioToApply);
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
                this.selectedRatio = null;
                this.baseRatio = null;
                this.isFlipped = false;
                this.cropper.setAspectRatio(NaN);
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
