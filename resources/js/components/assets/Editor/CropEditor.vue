<script setup>
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { onBeforeUnmount, ref, useTemplateRef, watch, nextTick } from 'vue';
import { Stack, Heading, Button, Select } from '@ui';
import { toast, keys } from '@api';

const emit = defineEmits(['cropped', 'update:open']);

const props = defineProps({
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
});

const cropper = ref(null);
const selectedRatio = ref(null);
const baseRatio = ref(null);
const isFlipped = ref(false);
const escBinding = ref(null);
const enterBinding = ref(null);
const isOptionKeyPressed = ref(false);
const initialCropBoxCenter = ref(null);
const isAdjustingCropBox = ref(false);
const animationFrameId = ref(null);
const cropperEventHandlers = ref(null);
const imageRef = useTemplateRef('image');

const aspectRatios = ref([
    { label: '16:9', value: 16 / 9 },
    { label: '4:3', value: 4 / 3 },
    { label: '3:2', value: 3 / 2 },
    { label: '2:1', value: 2 / 1 },
    { label: '1:1', value: 1 },
]);

watch(() => props.open, (newValue) => {
    if (newValue) {
        // Wait for Stack to open and image to be visible
        nextTick(() => setTimeout(() => initCropper(), 100));
        // Bind keyboard shortcuts when editor opens
        bindKeyboardShortcuts();
    } else {
        cleanup();
    }
});

onBeforeUnmount(() => cleanup());

function cleanup() {
    unbindKeyboardShortcuts();
    destroyCropper();
    resetState();
}

function resetState() {
    selectedRatio.value = null;
    baseRatio.value = null;
    isFlipped.value = false;
    isAdjustingCropBox.value = false;
    initialCropBoxCenter.value = null;
}

function destroyCropper() {
    removeCropperEvents();
    if (cropper.value) {
        cropper.value.destroy();
        cropper.value = null;
    }
}

function initCropper() {
    const imageElement = imageRef.value;
    if (!imageElement) return;

    destroyCropper();

    // Set crossOrigin attribute to handle CORS images
    // This allows canvas operations on cross-origin images if CORS headers are present
    if (imageElement.crossOrigin === null) {
        imageElement.crossOrigin = 'anonymous';
    }

    // Wait for image to load if not already loaded
    if (imageElement.complete) {
        createCropper(imageElement);
    } else {
        imageElement.addEventListener('load', () => {
            createCropper(imageElement);
        }, { once: true });
    }
}

function createCropper(imageElement) {
    try {
        cropper.value = new Cropper(imageElement, {
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
        setupCropperEvents();
    } catch (error) {
        // Handle SecurityError from tainted canvas (cross-origin without CORS)
        if (error.name === 'SecurityError' || error.message?.includes('tainted') || error.message?.includes('cross-origin')) {
            toast.error(__('Unable to crop image from external source. The image must be served with proper CORS headers.'));
            close();
        } else {
            // Re-throw other errors
            throw error;
        }
    }
}

function setupCropperEvents() {
    if (!cropper.value) return;

    const imageElement = imageRef.value;
    if (!imageElement) return;

    const onCropStart = () => {
        const cropBoxData = cropper.value.getCropBoxData();
        initialCropBoxCenter.value = {
            x: cropBoxData.left + cropBoxData.width / 2,
            y: cropBoxData.top + cropBoxData.height / 2,
        };
        isAdjustingCropBox.value = false;
    };

    // Adjust crop box position to maintain center when Option/Alt is held during resize
    // Use requestAnimationFrame to throttle updates and prevent lag
    const onCropMove = () => {
        if (!isOptionKeyPressed.value || !initialCropBoxCenter.value || isAdjustingCropBox.value) {
            return;
        }

        // Cancel any pending animation frame
        if (animationFrameId.value) {
            cancelAnimationFrame(animationFrameId.value);
        }

        // Schedule update on next animation frame to throttle
        animationFrameId.value = requestAnimationFrame(() => {
            adjustCropBoxCenter();
        });
    };

    const onCropEnd = () => {
        if (animationFrameId.value) {
            cancelAnimationFrame(animationFrameId.value);
            animationFrameId.value = null;
        }
        initialCropBoxCenter.value = null;
        isAdjustingCropBox.value = false;
    };

    imageElement.addEventListener('cropstart', onCropStart);
    imageElement.addEventListener('cropmove', onCropMove);
    imageElement.addEventListener('cropend', onCropEnd);

    cropperEventHandlers.value = { element: imageElement, onCropStart, onCropMove, onCropEnd };
}

function removeCropperEvents() {
    if (!cropperEventHandlers.value) return;

    const { element, onCropStart, onCropMove, onCropEnd } = cropperEventHandlers.value;
    element.removeEventListener('cropstart', onCropStart);
    element.removeEventListener('cropmove', onCropMove);
    element.removeEventListener('cropend', onCropEnd);
    cropperEventHandlers.value = null;
}

function adjustCropBoxCenter() {
    if (!cropper.value || !isOptionKeyPressed.value || !initialCropBoxCenter.value || isAdjustingCropBox.value) {
        return;
    }

    isAdjustingCropBox.value = true;

    const cropBoxData = cropper.value.getCropBoxData();
    const currentCenter = {
        x: cropBoxData.left + cropBoxData.width / 2,
        y: cropBoxData.top + cropBoxData.height / 2,
    };

    // Calculate how far the center has moved
    const centerDeltaX = currentCenter.x - initialCropBoxCenter.value.x;
    const centerDeltaY = currentCenter.y - initialCropBoxCenter.value.y;

    // Only adjust if center has moved significantly (more than 1px)
    if (Math.abs(centerDeltaX) > 1 || Math.abs(centerDeltaY) > 1) {
        // Adjust position to maintain the original center
        const newLeft = initialCropBoxCenter.value.x - cropBoxData.width / 2;
        const newTop = initialCropBoxCenter.value.y - cropBoxData.height / 2;

        cropper.value.setCropBoxData({
            left: newLeft,
            top: newTop,
            width: cropBoxData.width,
            height: cropBoxData.height,
        });
    }

    isAdjustingCropBox.value = false;
}

function setAspectRatio(ratio) {
    if (!cropper.value) return;

    if (ratio === null) {
        cropper.value.setAspectRatio(NaN);
        baseRatio.value = null;
        isFlipped.value = false;
    } else {
        baseRatio.value = ratio;
        isFlipped.value = false;
        applyCurrentRatio();
        // Expand crop box to fill available space
        expandCropBoxToFill();
    }
}

function toggleOrientation() {
    if (!cropper.value || baseRatio.value === null) return;

    // Toggle the flipped state
    isFlipped.value = !isFlipped.value;
    applyCurrentRatio();
    // Expand crop box to fill available space after flipping
    expandCropBoxToFill();
}

function applyCurrentRatio() {
    if (!cropper.value || baseRatio.value === null) return;

    const ratioToApply = isFlipped.value ? 1 / baseRatio.value : baseRatio.value;

    // Find if the ratio to apply matches one in our list
    const matchingRatio = aspectRatios.value.find(r => Math.abs(r.value - ratioToApply) < 0.001);

    if (matchingRatio && matchingRatio.value === ratioToApply) {
        // If the ratio is in our list, update the select to show it
        selectedRatio.value = matchingRatio.value;
    }

    // Apply the ratio to the cropper
    cropper.value.setAspectRatio(ratioToApply);
}

function expandCropBoxToFill() {
    if (!cropper.value) return;

    const canvasData = cropper.value.getCanvasData();

    // Calculate the maximum crop box size that fits within the canvas
    // while maintaining the aspect ratio
    let cropWidth = canvasData.width;
    let cropHeight = canvasData.height;

    if (baseRatio.value !== null) {
        const ratioToApply = isFlipped.value ? 1 / baseRatio.value : baseRatio.value;

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

    cropper.value.setCropBoxData({
        left,
        top,
        width: cropWidth,
        height: cropHeight,
    });
}

function crop() {
    if (!cropper.value) return;

    try {
        // Get crop box data in natural image coordinates
        const cropBoxData = cropper.value.getCropBoxData();
        const imageData = cropper.value.getImageData();

        // Calculate the crop dimensions in natural image coordinates
        // Scale from display coordinates to natural coordinates
        const scaleX = imageData.naturalWidth / imageData.width;
        const scaleY = imageData.naturalHeight / imageData.height;

        const naturalCropWidth = cropBoxData.width * scaleX;
        const naturalCropHeight = cropBoxData.height * scaleY;

        // Use the calculated dimensions to preserve aspect ratio
        const canvas = cropper.value.getCroppedCanvas({
            width: naturalCropWidth,
            height: naturalCropHeight,
        });

        if (!canvas) {
            toast.error(__('Failed to crop image'));
            return;
        }

        // Determine quality based on format (PNG doesn't use quality parameter)
        const outputMimeType = props.mimeType;
        const quality = outputMimeType === 'image/jpeg' || outputMimeType === 'image/webp' ? 0.95 : undefined;

        canvas.toBlob((blob) => {
            if (!blob) {
                toast.error(__('Failed to create cropped image'));
                return;
            }

            // Determine file extension from MIME type
            const extensionMap = {
                'image/jpeg': 'jpg',
                'image/png': 'png',
                'image/webp': 'webp',
            };
            const extension = extensionMap[outputMimeType] || 'png';

            // Convert blob to File object with correct MIME type and extension
            // This ensures the server can properly validate the file
            const file = new File([blob], `cropped-image.${extension}`, { type: outputMimeType });

            emit('cropped', { blob: file, mimeType: outputMimeType });
            close();
        }, outputMimeType, quality);
    } catch (error) {
        // Handle SecurityError from tainted canvas (cross-origin without CORS)
        if (error.name === 'SecurityError' || error.message?.includes('tainted') || error.message?.includes('cross-origin')) {
            toast.error(__('Unable to crop image from external source. The image must be served with proper CORS headers.'));
            return;
        }
        // Re-throw other errors
        throw error;
    }
}

function reset() {
    if (cropper.value) {
        resetState();
        cropper.value.setAspectRatio(NaN);
        // Reset to full canvas (image) bounds
        const canvasData = cropper.value.getCanvasData();
        cropper.value.setCropBoxData({
            left: canvasData.left,
            top: canvasData.top,
            width: canvasData.width,
            height: canvasData.height,
        });
    }
}

function bindKeyboardShortcuts() {
    // Escape to close
    escBinding.value = keys.bindGlobal('esc', (e) => {
        e.preventDefault();
        close();
    });

    // Enter to finish (only if cropper is ready and not in a form field)
    enterBinding.value = keys.bindGlobal('enter', (e) => {
        if (cropper.value && !e.shiftKey && !e.ctrlKey && !e.metaKey) {
            // Check if focus is not in an input/textarea/select
            const activeElement = document.activeElement;
            const isInFormField = activeElement && (
                activeElement.tagName === 'INPUT' ||
                activeElement.tagName === 'TEXTAREA' ||
                activeElement.tagName === 'SELECT'
            );
            if (!isInFormField) {
                e.preventDefault();
                crop();
            }
        }
    });

    // Track Option/Alt key for center-based resizing
    window.addEventListener('keydown', handleKeyDown);
    window.addEventListener('keyup', handleKeyUp);
}

function handleKeyDown(event) {
    // Track Option key (Alt on Windows/Linux, Option on Mac)
    // On Mac, Option key produces event.key === 'Alt', not 'Meta'
    if (event.key === 'Alt' || event.altKey) {
        isOptionKeyPressed.value = true;
    }
}

function handleKeyUp(event) {
    // Release Option key tracking
    // On Mac, Option key produces event.key === 'Alt', not 'Meta'
    if (event.key === 'Alt') {
        isOptionKeyPressed.value = false;
    }
}

function unbindKeyboardShortcuts() {
    if (escBinding.value) {
        escBinding.value.destroy();
        escBinding.value = null;
    }
    if (enterBinding.value) {
        enterBinding.value.destroy();
        enterBinding.value = null;
    }
    // Remove Option/Alt key listeners
    window.removeEventListener('keydown', handleKeyDown);
    window.removeEventListener('keyup', handleKeyUp);
    // Cancel any pending animation frames
    if (animationFrameId.value) {
        cancelAnimationFrame(animationFrameId.value);
        animationFrameId.value = null;
    }
    isOptionKeyPressed.value = false;
    isAdjustingCropBox.value = false;
}

function close() {
    emit('update:open', false);
}
</script>

<style>
.cropper-bg {
    background-image: none !important;
}
</style>

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
