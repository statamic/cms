<script setup>
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { computed, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { Button, Heading, Icon, Modal, Select, Stack } from '@ui';
import { keys, toast } from '@api';
import wait from '@/util/wait';
import axios from 'axios';

const emit = defineEmits(['replaced', 'created', 'update:open']);

const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    open: {
        type: Boolean,
        default: false,
    },
    canReplace: {
        type: Boolean,
        default: false
    },
});

const cropper = ref(null);
const selectedRatio = ref(null);
const baseRatio = ref(null);
const isFlipped = ref(false);
const enterBinding = ref(null);
const isOptionKeyPressed = ref(false);
const initialCropBoxCenter = ref(null);
const isAdjustingCropBox = ref(false);
const animationFrameId = ref(null);
const imageRef = useTemplateRef('image');
const showConfirmation = ref(false);
const uploading = ref(false);
const pendingBlob = ref(null);
const pendingMimeType = ref(null);

const aspectRatios = ref([
    { label: '16:9', value: 16 / 9 },
    { label: '4:3', value: 4 / 3 },
    { label: '3:2', value: 3 / 2 },
    { label: '2:1', value: 2 / 1 },
    { label: '1:1', value: 1 },
]);

watch(() => props.open, (newValue) => {
    if (newValue) {
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
    showConfirmation.value = false;
    uploading.value = false;
    pendingBlob.value = null;
    pendingMimeType.value = null;
}

function destroyCropper() {
    if (cropper.value) {
        cropper.value.destroy();
        cropper.value = null;
    }
}

const crossOrigin = computed(() => {
    try {
        return new URL(props.asset.preview, window.location.href).origin !== window.location.origin ? 'anonymous' : null;
    } catch {
        return null;
    }
});

function onImageError() {
    if (crossOrigin.value) {
        toast.error(__('Unable to crop image from external source. The image must be served with proper CORS headers.'));
        close();
    }
}

function initCropper() {
    destroyCropper();
    imageRef.value.decode().then(() => createCropper(imageRef.value));
}

function createCropper(imageElement) {
    cropper.value = new Cropper(imageElement, {
        aspectRatio: NaN,
        viewMode: 1,
        dragMode: 'crop',
        autoCropArea: 0.9,
        restore: false,
        guides: true,
        center: true,
        highlight: true,
        cropBoxMovable: true,
        cropBoxResizable: true,
        zoomable: true,
        zoomOnTouch: true,
        zoomOnWheel: true,
        scalable: false,
        rotatable: false,
        responsive: true,
        movable: false,
        cropstart: onCropStart,
        cropmove: onCropMove,
        cropend: onCropEnd,
    });
}

function onCropStart() {
    const cropBoxData = cropper.value.getCropBoxData();
    initialCropBoxCenter.value = {
        x: cropBoxData.left + cropBoxData.width / 2,
        y: cropBoxData.top + cropBoxData.height / 2,
    };
    isAdjustingCropBox.value = false;
}

// Adjust crop box position to maintain center when Option/Alt is held during resize
// Use requestAnimationFrame to throttle updates and prevent lag
function onCropMove() {
    if (!isOptionKeyPressed.value || !initialCropBoxCenter.value || isAdjustingCropBox.value) {
        return;
    }

    if (animationFrameId.value) {
        cancelAnimationFrame(animationFrameId.value);
    }

    animationFrameId.value = requestAnimationFrame(() => {
        adjustCropBoxCenter();
    });
}

function onCropEnd() {
    if (animationFrameId.value) {
        cancelAnimationFrame(animationFrameId.value);
        animationFrameId.value = null;
    }
    initialCropBoxCenter.value = null;
    isAdjustingCropBox.value = false;
}


function adjustCropBoxCenter() {
    if (!isOptionKeyPressed.value || !initialCropBoxCenter.value || isAdjustingCropBox.value) {
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
    if (baseRatio.value === null) return;

    // Toggle the flipped state
    isFlipped.value = !isFlipped.value;
    applyCurrentRatio();
    // Expand crop box to fill available space after flipping
    expandCropBoxToFill();
}

function applyCurrentRatio() {
    if (baseRatio.value === null) return;

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
    const cropBoxData = cropper.value.getCropBoxData();
    const imageData = cropper.value.getImageData();

    const scaleX = imageData.naturalWidth / imageData.width;
    const scaleY = imageData.naturalHeight / imageData.height;

    const canvas = cropper.value.getCroppedCanvas({
        width: cropBoxData.width * scaleX,
        height: cropBoxData.height * scaleY,
    });

    if (!canvas) {
        toast.error(__('Failed to crop image'));
        return;
    }

    const outputMimeType = props.asset.mimeType;
    const quality = outputMimeType === 'image/jpeg' || outputMimeType === 'image/webp' ? 0.95 : undefined;

    canvas.toBlob((blob) => {
        if (!blob) {
            toast.error(__('Failed to create cropped image'));
            return;
        }

        const extensionMap = {
            'image/jpeg': 'jpg',
            'image/png': 'png',
            'image/webp': 'webp',
        };
        const extension = extensionMap[outputMimeType] || 'png';
        pendingBlob.value = new File([blob], `cropped-image.${extension}`, { type: outputMimeType });
        pendingMimeType.value = outputMimeType;
        showConfirmation.value = true;
    }, outputMimeType, quality);
}

function reset() {
    resetState();
    cropper.value.reset();
    cropper.value.setAspectRatio(NaN);
}
function bindKeyboardShortcuts() {
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

async function upload(replaceOriginal) {
    if (!pendingBlob.value) return;

    uploading.value = true;

    try {
        const [containerHandle, assetPath] = props.asset.id.split('::');
        const pathParts = assetPath.split('/');
        let filename = pathParts.pop();
        const folder = pathParts.length > 0 ? pathParts.join('/') : '/';

        if (!replaceOriginal && pendingMimeType.value && pendingMimeType.value !== props.asset.mimeType) {
            const extensionMap = { 'image/jpeg': '.jpg', 'image/png': '.png', 'image/webp': '.webp' };
            const newExtension = extensionMap[pendingMimeType.value];
            if (newExtension) {
                filename = filename.replace(/\.[^/.]+$/, '') + newExtension;
            }
        }

        const formData = new FormData();
        const fileToUpload = filename !== pendingBlob.value.name
            ? new File([pendingBlob.value], filename, { type: pendingBlob.value.type })
            : pendingBlob.value;
        formData.append('file', fileToUpload);
        formData.append('container', containerHandle);
        formData.append('folder', folder);
        formData.append('_token', Statamic.$config.get('csrfToken'));
        formData.append('option', replaceOriginal ? 'overwrite' : 'timestamp');

        const url = cp_url('assets');
        const response = await axios.post(url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.data?.data) {
            showConfirmation.value = false;
            pendingBlob.value = null;
            pendingMimeType.value = null;
            close();
            await wait(300); // wait for this cropper stack to close.

            if (replaceOriginal) {
                toast.success(__('Image replaced successfully'));
                emit('replaced');
            } else {
                toast.success(__('Cropped image saved successfully'));
                emit('created', response.data.data.id);
            }
        }
    } catch (error) {
        toast.error(error.response?.data?.message || __('Failed to upload cropped image'));
    } finally {
        uploading.value = false;
    }
}

function dismissConfirmation() {
    showConfirmation.value = false;
    pendingBlob.value = null;
    pendingMimeType.value = null;
}

function close() {
    emit('update:open', false);
}
</script>

<template>
    <Stack size="full" :open="open" inset @update:open="$emit('update:open', $event)" @opened="initCropper" :show-close-button="false">
        <div class="min-h-0 flex h-full flex-col bg-gray-100 dark:bg-gray-850">
            <!-- Header -->
            <header class="relative flex w-full items-center justify-between px-4 py-3 border-b dark:border-gray-700">
                <Heading size="lg">{{ __('Crop Image') }}</Heading>
                <ui-button variant="ghost" icon="x" round @click="close" :aria-label="__('Close')" />
            </header>

            <!-- Content -->
            <div class="bg-gray-300 p-3 inset-shadow-xs dark:bg-gray-850 flex flex-1 flex-col overflow-auto relative min-h-0 w-full items-center justify-center" role="img" :aria-label="__('Image crop area')">
                <div class="h-full w-full min-h-0 flex items-center justify-center overflow-hidden">
                    <img ref="image" :src="asset.preview" :crossorigin="crossOrigin" :alt="__('Image to crop')" class="max-w-full max-h-full" @error="onImageError" />
                </div>
            </div>

            <!-- Footer -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-t dark:border-gray-700 px-4 py-3">
                <div class="flex gap-3">
                    <Select
                        clearable
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
                        v-if="selectedRatio"
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
            <Modal
                :open="showConfirmation"
                :title="__('Save Cropped Image')"
                :dismissible="!uploading"
                @update:open="(open) => { if (!open) dismissConfirmation(); }"
            >
                <div
                    v-if="uploading"
                    class="pointer-events-none absolute inset-0 flex select-none items-center justify-center bg-white bg-opacity-75 dark:bg-gray-850"
                >
                    <Icon name="loading" />
                </div>

                <p>{{ canReplace ? __('messages.crop_save_copy_or_replace') : __('messages.crop_save_as_copy_confirm') }}</p>

                <template #footer>
                    <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                        <Button
                            variant="ghost"
                            :disabled="uploading"
                            :text="__('Cancel')"
                            @click="dismissConfirmation"
                        />
                        <Button
                            :variant="canReplace ? 'default' : 'primary'"
                            :disabled="uploading"
                            :text="__('Save as Copy')"
                            @click="upload(false)"
                        />
                        <Button
                            v-if="canReplace"
                            variant="primary"
                            :disabled="uploading"
                            :text="__('Replace Original')"
                            @click="upload(true)"
                        />
                    </div>
                </template>
            </Modal>
        </div>
    </Stack>
</template>
