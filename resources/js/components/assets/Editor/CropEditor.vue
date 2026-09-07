<script setup>
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { computed, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { Button, Heading, Icon, Modal, Radio, RadioGroup, Select, Slider, Stack, Field, Subheading } from '@ui';
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
const pendingCrop = ref(null);
const cropDimensions = ref(null);
const defaultQuality = Statamic.$config.get('cropQuality') || 90;

function normalizeFormat(format) {
    format = (format || '').toLowerCase();
    return format === 'jpeg' ? 'jpg' : format;
}

// The source image's format, normalized (e.g. jpeg -> jpg).
const sourceFormat = computed(() => normalizeFormat(props.asset.extension));

// We only offer quality/conversion controls for these source types.
const isConvertible = computed(() => ['jpg', 'png', 'webp', 'avif'].includes(sourceFormat.value));

// PNG is lossless so its quality starts maxed out; lowering it implies the
// user wants to convert to a lossy format.
const initialQuality = () => (sourceFormat.value === 'png' ? 100 : defaultQuality);

const format = ref(sourceFormat.value);
const quality = ref(initialQuality());
const background = ref('white');

const aspectRatios = ref(Statamic.$config.get('cropAspectRatios') || []);

const formatLabels = { jpg: 'JPEG', png: 'PNG', webp: 'WebP', avif: 'AVIF' };

const formatOptions = computed(() => {
    // Lossy conversion targets, plus the source format so it can be kept as-is.
    return [...new Set([sourceFormat.value, 'jpg', 'webp'])].map((value) => ({
        value,
        label: formatLabels[value] ?? value.toUpperCase(),
    }));
});

// A quality setting only applies to lossy output formats.
const outputUsesQuality = computed(() => ['jpg', 'webp', 'avif'].includes(format.value));

const showQuality = computed(() => isConvertible.value);

const showFormatSelector = computed(() => isConvertible.value);

// JPEG has no alpha channel, so a potentially-transparent source needs a background colour.
const showBackground = computed(() => format.value === 'jpg' && ['png', 'webp', 'avif'].includes(sourceFormat.value));

// Changing the format writes a different extension, which can't overwrite the original.
const canReplaceOutput = computed(() => props.canReplace && format.value === sourceFormat.value);

watch(format, (value) => {
    if (value === 'png') quality.value = 100;
});

watch(quality, (value) => {
    // Lowering a PNG's quality implies the user wants compression, so switch to
    // WebP — it's lossy but, unlike JPEG, keeps the transparency.
    if (sourceFormat.value === 'png' && format.value === 'png' && value < 100) {
        format.value = 'webp';
    }
});

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
    pendingCrop.value = null;
    cropDimensions.value = null;
    quality.value = initialQuality();
    format.value = sourceFormat.value;
    background.value = 'white';
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
        crop: onCrop,
    });
}

function onCrop(event) {
    cropDimensions.value = {
        width: Math.round(event.detail.width),
        height: Math.round(event.detail.height),
    };
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
    // Crop box coordinates relative to the original image's natural dimensions.
    // The actual cropping happens server-side from the original file.
    const data = cropper.value.getData(true);

    if (!data || data.width < 1 || data.height < 1) {
        toast.error(__('Failed to crop image'));
        return;
    }

    pendingCrop.value = {
        x: Math.max(0, data.x),
        y: Math.max(0, data.y),
        width: data.width,
        height: data.height,
    };

    showConfirmation.value = true;
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

// Cropped output is small, so it uses the single-request endpoint rather than chunked uploads.
async function upload(replaceOriginal) {
    if (!pendingCrop.value) return;

    uploading.value = true;

    try {
        const response = await axios.post(props.asset.cropUrl, {
            ...pendingCrop.value,
            format: format.value,
            quality: outputUsesQuality.value ? quality.value : null,
            background: showBackground.value ? background.value : null,
            replace: replaceOriginal,
        });

        if (response.data?.data) {
            showConfirmation.value = false;
            pendingCrop.value = null;
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
    pendingCrop.value = null;
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
                <div
                    v-if="cropDimensions"
                    class="absolute top-5 end-5 z-10 rounded-md bg-gray-900/75 px-2 py-1 text-xs font-medium text-white tabular-nums pointer-events-none"
                    :aria-label="__('Dimensions')"
                    v-text="__('messages.width_x_height', { width: cropDimensions.width, height: cropDimensions.height })"
                />

            </div>

            <!-- Footer -->
            <div class="flex flex-wrap items-center gap-3 border-t dark:border-gray-700 px-4 py-3">
                <div v-if="aspectRatios.length" class="flex gap-3">
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
                        adaptive-width
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
                <div class="flex gap-3 ms-auto">
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

                <p>{{ canReplaceOutput ? __('messages.crop_save_copy_or_replace') : __('messages.crop_save_as_copy_confirm') }}</p>

                <div v-if="showQuality" class="mt-6 space-y-4">
                    <div class="flex justify-between gap-4">
                        <Field
                            id="crop-quality"
                            :label="__('Quality')"
                            class="w-1/2 space-y-2"
                        >
                            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg p-2 @lg:px-4 @lg:py-3 with-contrast:border with-contrast:border-gray-500">
                                <Slider id="crop-quality" v-model="quality" :min="1" :max="100" :aria-label="__('Quality')" />
                                <Subheading :text="`${quality}%`" class="w-14 justify-center" />
                            </div>
                        </Field>

                        <Field
                            v-if="showFormatSelector"
                            class="w-1/2 space-y-2"
                            id="crop-format"
                            :label="__('Format')"
                        >
                            <Select
                                id="crop-format"
                                v-model="format"
                                :options="formatOptions"
                                option-label="label"
                                option-value="value"
                                size="sm"
                                :aria-label="__('Output format')"
                            />
                        </Field>
                    </div>

                    <div v-if="showBackground">
                        <Field
                            id="crop-background"
                            :label="__('Background')"
                            :instructions="__('messages.crop_jpeg_background_help')"
                        >
                            <RadioGroup v-model="background" appearance="inline" :aria-label="__('Background colour')">
                                <Radio value="white" :label="__('White')" />
                                <Radio value="black" :label="__('Black')" />
                            </RadioGroup>
                        </Field>
                    </div>
                </div>

                <template #footer>
                    <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                        <Button
                            variant="ghost"
                            :disabled="uploading"
                            :text="__('Cancel')"
                            @click="dismissConfirmation"
                        />
                        <Button
                            :disabled="uploading"
                            :text="__('Save as Copy')"
                            @click="upload(false)"
                        />
                        <Button
                            v-if="canReplace"
                            variant="primary"
                            :disabled="uploading || !canReplaceOutput"
                            :text="__('Replace Original')"
                            v-tooltip="canReplace && !canReplaceOutput ? __('messages.crop_replace_unavailable_format') : null"
                            @click="upload(true)"
                        />
                    </div>
                </template>
            </Modal>
        </div>
    </Stack>
</template>
