<template>
    <Stack size="full" :open="open" inset @update:open="$emit('update:open', $event)" :show-close-button="false">
        <div class="min-h-0 flex h-full flex-col bg-gray-100 dark:bg-dark-800">
            <!-- Header -->
            <header class="relative flex w-full items-center justify-between px-4 py-3 border-b dark:border-gray-700">
                <Heading size="lg">{{ __('Crop Image') }}</Heading>
                <ui-button variant="ghost" icon="x" round @click="close" :aria-label="__('Close')" />
            </header>

            <!-- Content -->
            <div class="flex flex-1 flex-col overflow-auto bg-black relative min-h-0 w-full items-center justify-center" role="img" :aria-label="__('Image crop area')">
                <div class="px-3 lg:px-6 min-h-0">
                    <img ref="image" :src="image" :alt="__('Image to crop')" class="max-w-full max-h-full" />
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
                        :aria-label="__('Select aspect ratio')"
                        @update:modelValue="setAspectRatio"
                    />
                    <Button
                        v-if="selectedRatio !== null"
                        icon="flip-vertical"
                        :variant="isFlipped ? 'pressed' : 'ghost'"
                        size="sm"
                        :text="__('Flip Orientation')"
                        :aria-label="__('Flip crop orientation')"
                        :aria-pressed="isFlipped"
                        @click="toggleOrientation"
                    />
                </div>
                <div class="flex gap-3">
                    <Button variant="ghost" :text="__('Cancel')" :aria-label="__('Cancel cropping')" @click="close" />
                    <Button variant="ghost" :text="__('Reset')" :aria-label="__('Reset crop selection')" @click="reset" />
                    <Button variant="primary" :text="__('Finish')" :aria-label="__('Finish cropping')" :disabled="!cropper" @click="crop" />
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
            escBinding: null,
            enterBinding: null,
            isOptionKeyPressed: false,
            initialCropBoxCenter: null,
            initialCropBoxSize: null,
            isAdjustingCropBox: false,
            animationFrameId: null,
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
                // Bind keyboard shortcuts when editor opens
                this.bindKeyboardShortcuts();
            } else {
                // Unbind keyboard shortcuts when editor closes
                this.unbindKeyboardShortcuts();
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                    // Reset state to initial values
                    this.selectedRatio = null;
                    this.baseRatio = null;
                    this.isFlipped = false;
                }
            }
        },
    },

    beforeUnmount() {
        this.unbindKeyboardShortcuts();
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

            // Set crossOrigin attribute to handle CORS images
            // This allows canvas operations on cross-origin images if CORS headers are present
            if (imageElement.crossOrigin === '') {
                imageElement.crossOrigin = 'anonymous';
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
            try {
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

                // Add event listeners for center-based resizing with Option/Alt key
                this.setupCropperEvents();
            } catch (error) {
                // Handle SecurityError from tainted canvas (cross-origin without CORS)
                if (error.name === 'SecurityError' || error.message?.includes('tainted') || error.message?.includes('cross-origin')) {
                    this.$toast.error(__('Unable to crop image from external source. The image must be served with proper CORS headers.'));
                    this.close();
                } else {
                    // Re-throw other errors
                    throw error;
                }
            }
        },

        setupCropperEvents() {
            if (!this.cropper) return;

            const imageElement = this.$refs.image;
            if (!imageElement) return;

            // Store initial crop box center and size when crop starts
            imageElement.addEventListener('cropstart', () => {
                const cropBoxData = this.cropper.getCropBoxData();
                this.initialCropBoxCenter = {
                    x: cropBoxData.left + cropBoxData.width / 2,
                    y: cropBoxData.top + cropBoxData.height / 2,
                };
                this.initialCropBoxSize = {
                    width: cropBoxData.width,
                    height: cropBoxData.height,
                };
                this.isAdjustingCropBox = false;
            });

            // Adjust crop box position to maintain center when Option/Alt is held during resize
            // Use requestAnimationFrame to throttle updates and prevent lag
            imageElement.addEventListener('cropmove', () => {
                if (!this.isOptionKeyPressed || !this.initialCropBoxCenter || this.isAdjustingCropBox) {
                    return;
                }

                // Cancel any pending animation frame
                if (this.animationFrameId) {
                    cancelAnimationFrame(this.animationFrameId);
                }

                // Schedule update on next animation frame to throttle
                this.animationFrameId = requestAnimationFrame(() => {
                    this.adjustCropBoxCenter();
                });
            });

            // Reset tracking when crop ends
            imageElement.addEventListener('cropend', () => {
                if (this.animationFrameId) {
                    cancelAnimationFrame(this.animationFrameId);
                    this.animationFrameId = null;
                }
                this.initialCropBoxCenter = null;
                this.initialCropBoxSize = null;
                this.isAdjustingCropBox = false;
            });
        },

        adjustCropBoxCenter() {
            if (!this.cropper || !this.isOptionKeyPressed || !this.initialCropBoxCenter || this.isAdjustingCropBox) {
                return;
            }

            this.isAdjustingCropBox = true;

            const cropBoxData = this.cropper.getCropBoxData();
            const currentCenter = {
                x: cropBoxData.left + cropBoxData.width / 2,
                y: cropBoxData.top + cropBoxData.height / 2,
            };

            // Calculate how far the center has moved
            const centerDeltaX = currentCenter.x - this.initialCropBoxCenter.x;
            const centerDeltaY = currentCenter.y - this.initialCropBoxCenter.y;

            // Only adjust if center has moved significantly (more than 1px)
            if (Math.abs(centerDeltaX) > 1 || Math.abs(centerDeltaY) > 1) {
                // Adjust position to maintain the original center
                const newLeft = this.initialCropBoxCenter.x - cropBoxData.width / 2;
                const newTop = this.initialCropBoxCenter.y - cropBoxData.height / 2;

                this.cropper.setCropBoxData({
                    left: newLeft,
                    top: newTop,
                    width: cropBoxData.width,
                    height: cropBoxData.height,
                });
            }

            this.isAdjustingCropBox = false;
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

            try {
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
                // Note: canvas.toBlob() doesn't support GIF - browsers silently fall back to PNG
                // So we need to convert GIF to PNG to match what's actually produced
                let outputMimeType = this.mimeType;
                if (outputMimeType === 'image/gif') {
                    outputMimeType = 'image/png';
                }

                const quality = outputMimeType === 'image/jpeg' || outputMimeType === 'image/webp' ? 0.95 : undefined;

                canvas.toBlob((blob) => {
                    if (!blob) {
                        this.$toast.error(__('Failed to create cropped image'));
                        return;
                    }

                    // Determine file extension from MIME type
                    const extensionMap = {
                        'image/jpeg': 'jpg',
                        'image/png': 'png',
                        'image/webp': 'webp',
                        'image/gif': 'gif',
                    };
                    const extension = extensionMap[outputMimeType] || 'png';

                    // Convert blob to File object with correct MIME type and extension
                    // This ensures the server can properly validate the file
                    const file = new File([blob], `cropped-image.${extension}`, { type: outputMimeType });

                    this.$emit('cropped', { blob: file, mimeType: outputMimeType });
                    this.close();
                }, outputMimeType, quality);
            } catch (error) {
                // Handle SecurityError from tainted canvas (cross-origin without CORS)
                if (error.name === 'SecurityError' || error.message?.includes('tainted') || error.message?.includes('cross-origin')) {
                    this.$toast.error(__('Unable to crop image from external source. The image must be served with proper CORS headers.'));
                    return;
                }
                // Re-throw other errors
                throw error;
            }
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

        bindKeyboardShortcuts() {
            // Escape to close
            this.escBinding = this.$keys.bindGlobal('esc', (e) => {
                e.preventDefault();
                this.close();
            });

            // Enter to finish (only if cropper is ready and not in a form field)
            this.enterBinding = this.$keys.bindGlobal('enter', (e) => {
                if (this.cropper && !e.shiftKey && !e.ctrlKey && !e.metaKey) {
                    // Check if focus is not in an input/textarea/select
                    const activeElement = document.activeElement;
                    const isInFormField = activeElement && (
                        activeElement.tagName === 'INPUT' ||
                        activeElement.tagName === 'TEXTAREA' ||
                        activeElement.tagName === 'SELECT'
                    );
                    if (!isInFormField) {
                        e.preventDefault();
                        this.crop();
                    }
                }
            });

            // Track Option/Alt key for center-based resizing
            window.addEventListener('keydown', this.handleKeyDown);
            window.addEventListener('keyup', this.handleKeyUp);
        },

        handleKeyDown(event) {
            // Track Option key (Alt on Windows/Linux, Option on Mac)
            // On Mac, Option key produces event.key === 'Alt', not 'Meta'
            if (event.key === 'Alt' || event.altKey) {
                this.isOptionKeyPressed = true;
            }
        },

        handleKeyUp(event) {
            // Release Option key tracking
            // On Mac, Option key produces event.key === 'Alt', not 'Meta'
            if (event.key === 'Alt') {
                this.isOptionKeyPressed = false;
            }
        },

        unbindKeyboardShortcuts() {
            if (this.escBinding) {
                this.escBinding.destroy();
                this.escBinding = null;
            }
            if (this.enterBinding) {
                this.enterBinding.destroy();
                this.enterBinding = null;
            }
            // Remove Option/Alt key listeners
            window.removeEventListener('keydown', this.handleKeyDown);
            window.removeEventListener('keyup', this.handleKeyUp);
            // Cancel any pending animation frames
            if (this.animationFrameId) {
                cancelAnimationFrame(this.animationFrameId);
                this.animationFrameId = null;
            }
            this.isOptionKeyPressed = false;
            this.isAdjustingCropBox = false;
        },

        close() {
            // Cancel any pending animation frames
            if (this.animationFrameId) {
                cancelAnimationFrame(this.animationFrameId);
                this.animationFrameId = null;
            }
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
            // Reset state to initial values
            this.selectedRatio = null;
            this.baseRatio = null;
            this.isFlipped = false;
            this.isAdjustingCropBox = false;
            this.initialCropBoxCenter = null;
            this.initialCropBoxSize = null;
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
