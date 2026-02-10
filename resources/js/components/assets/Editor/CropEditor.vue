<template>
    <Stack size="full" :open="open" inset @update:open="$emit('update:open', $event)" :show-close-button="false">
        <div class="min-h-0 flex h-full flex-col bg-gray-100 dark:bg-dark-800">
            <!-- Header -->
            <header class="relative flex w-full items-center justify-between px-4 py-3 border-b dark:border-gray-700">
                <Heading size="lg">{{ __('Crop Image') }}</Heading>
                <ui-button variant="ghost" icon="x" round @click="close" :aria-label="__('Close')" />
            </header>

            <!-- Content -->
            <div class="flex flex-1 flex-col overflow-auto bg-black relative min-h-0 w-full items-center justify-center">
                <div class="px-3 lg:px-6 min-h-0">
                    <img ref="image" :src="image" alt="Crop" class="max-w-full max-h-full" />
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between gap-3 border-t dark:border-gray-700 px-4 py-3">
                <div class="flex gap-3">
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
                <div class="flex gap-3">
                    <Button variant="ghost" :text="__('Cancel')" @click="close" />
                    <Button variant="ghost" :text="__('Reset')" @click="reset" />
                    <Button variant="primary" :text="__('Finish')" @click="crop" />
                </div>
            </div>
        </div>
    </Stack>
</template>

<script>
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import {
    Stack,
    Heading,
    Button,
    Select,
} from '@ui';

export default {
    emits: ['cropped', 'closed', 'update:open'],

    components: {
        Stack,
        Heading,
        Button,
        Select,
    },

    props: {
        image: {
            type: String,
            required: true,
        },
        mimeType: {
            type: String,
            required: true,
        },
        open: {
            type: Boolean,
            default: false,
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

    watch: {
        open(newValue) {
            if (newValue) {
                // Wait for Stack to open and image to be visible
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.initCropper();
                    }, 100);
                });
            } else if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
                // Reset state to initial values
                this.selectedRatio = null;
                this.baseRatio = null;
                this.isFlipped = false;
            }
        },
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

            // Destroy existing cropper if any
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }

            // Wait for image to load if not already loaded
            if (imageElement.complete) {
                this.createCropper(imageElement);
            } else {
                imageElement.addEventListener('load', () => {
                    this.createCropper(imageElement);
                }, { once: true });
            }
        },

        createCropper(imageElement) {
            this.cropper = new Cropper(imageElement, {
                aspectRatio: NaN,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
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
                // Expand crop box to fill available space
                this.expandCropBoxToFill();
            }
        },

        toggleOrientation() {
            if (!this.cropper || this.baseRatio === null) return;

            // Toggle the flipped state
            this.isFlipped = !this.isFlipped;
            this.applyCurrentRatio();
            // Expand crop box to fill available space after flipping
            this.expandCropBoxToFill();
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

        expandCropBoxToFill() {
            if (!this.cropper) return;

            const canvasData = this.cropper.getCanvasData();

            // Calculate the maximum crop box size that fits within the canvas
            // while maintaining the aspect ratio
            let cropWidth = canvasData.width;
            let cropHeight = canvasData.height;

            if (this.baseRatio !== null) {
                const ratioToApply = this.isFlipped ? 1 / this.baseRatio : this.baseRatio;

                // Calculate dimensions that fit within canvas while maintaining ratio
                if (canvasData.width / canvasData.height > ratioToApply) {
                    // Canvas is wider than ratio, fit to height
                    cropWidth = canvasData.height * ratioToApply;
                    cropHeight = canvasData.height;
                } else {
                    // Canvas is taller than ratio, fit to width
                    cropWidth = canvasData.width;
                    cropHeight = canvasData.width / ratioToApply;
                }
            }

            // Center the crop box
            const left = canvasData.left + (canvasData.width - cropWidth) / 2;
            const top = canvasData.top + (canvasData.height - cropHeight) / 2;

            this.cropper.setCropBoxData({
                left,
                top,
                width: cropWidth,
                height: cropHeight,
            });
        },

        crop() {
            if (!this.cropper) return;

            // Get crop box data in natural image coordinates
            const cropBoxData = this.cropper.getCropBoxData();
            const imageData = this.cropper.getImageData();

            // Calculate the crop dimensions in natural image coordinates
            // Scale from display coordinates to natural coordinates
            const scaleX = imageData.naturalWidth / imageData.width;
            const scaleY = imageData.naturalHeight / imageData.height;

            const naturalCropWidth = cropBoxData.width * scaleX;
            const naturalCropHeight = cropBoxData.height * scaleY;

            // Use the calculated dimensions to preserve aspect ratio
            const canvas = this.cropper.getCroppedCanvas({
                width: naturalCropWidth,
                height: naturalCropHeight,
            });

            if (!canvas) {
                this.$toast.error(__('Failed to crop image'));
                return;
            }

            // Determine quality based on format (PNG doesn't use quality parameter)
            const mimeType = this.mimeType;
            const quality = mimeType === 'image/jpeg' || mimeType === 'image/webp' ? 0.95 : undefined;

            canvas.toBlob((blob) => {
                if (!blob) {
                    this.$toast.error(__('Failed to create cropped image'));
                    return;
                }

                this.$emit('cropped', { blob, mimeType });
                this.close();
            }, mimeType, quality);
        },

        reset() {
            if (this.cropper) {
                this.selectedRatio = null;
                this.baseRatio = null;
                this.isFlipped = false;
                this.cropper.setAspectRatio(NaN);
                // Reset to full canvas (image) bounds
                const canvasData = this.cropper.getCanvasData();
                this.cropper.setCropBoxData({
                    left: canvasData.left,
                    top: canvasData.top,
                    width: canvasData.width,
                    height: canvasData.height,
                });
            }
        },

        close() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
            // Reset state to initial values
            this.selectedRatio = null;
            this.baseRatio = null;
            this.isFlipped = false;
            this.$emit('update:open', false);
            this.$emit('closed');
        },
    },
};
</script>

<style>
    .cropper-bg {
        background-image: none !important;
    }
</style>
