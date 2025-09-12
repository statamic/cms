<template>
    <div class="flex items-center">
        <!-- <div class="input-group w-auto" :class="{ 'max-w-[130px]': config.allow_any }"> -->
        <div class="flex items-center rounded-full relative border shadow-ui-sm">
            <ui-popover ref="colorPopover" name="swatches" direction="bottom" class="md:w-[320px]" :open="popoverOpen" @update:open="popoverOpen = $event">
                <template #trigger>
                    <button type="button" class="cursor-pointer size-9 border rounded-full flex items-center justify-center" :aria-label="__('Pick Color')">
                        <div
                            class="size-8 rounded-full"
                            :class="{ 'cursor-not-allowed': isReadOnly }"
                            :style="{ 'background-color': customColor || value }"
                        />
                    </button>
                </template>
                <template #default="{ close }">
                    <div class="">
                        <div v-if="config.swatches.length" class="grid grid-cols-6 gap-2">
                            <button
                                v-for="swatch in config.swatches"
                                type="button"
                                class="flex size-9 rounded-full cursor-pointer"
                                :style="{ 'background-color': swatch }"
                                @click="update(swatch)"
                            >
                                <div v-if="swatch === value" class="flex h-full w-full items-center justify-center">
                                    <div class="flex size-5 items-center justify-center rounded-full bg-black/10">
                                        <ui-icon name="checkmark" class="size-4 text-white" />
                                    </div>
                                </div>
                            </button>
                        </div>
                        <div
                            v-if="config.allow_any"
                            class="flex items-center gap-2"
                            :class="{ 'mt-4': config.swatches.length }"
                        >
                        <ui-input
                            type="color"
                            :value="customColor"
                            @input="customColorSelected"
                        />
                        <ui-button
                            :text="__('OK')"
                            variant="primary"
                            @click="handleOkClick"
                        />
                        </div>
                    </div>
                </template>
            </ui-popover>

            <input
                v-if="config.allow_any"
                class="font-mono text-sm px-2 w-24 outline-none"
                maxlength="7"
                type="text"
                :readonly="isReadOnly"
                :value="customColor"
                @input="updateDebounced($event.target.value)"
                @blur="sanitizeCustomColor"
            />
        </div>

        <ui-button v-if="value" icon="x" :aria-label="__('Reset')" @click="resetColor" round inset size="sm" variant="ghost" class="ms-1" />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
export default {
    mixins: [Fieldtype],

    data() {
        return {
            customColor: this.value,
            popoverOpen: false,
        };
    },

    watch: {
        value(value) {
            this.customColor = value;
        },
    },

    computed: {
        replicatorPreview() {
            if (!this.showFieldPreviews) return;

            return this.value
                ? replicatorPreviewHtml(`<span class="little-dot" style="background-color:${this.value}"></span>`)
                : null;
        },
    },

    methods: {
        customColorSelected(event) {
            this.customColor = event.target.value;
        },

        sanitizeCustomColor() {
            this.customColor = this.sanitizeColor(this.customColor);
            this.update(this.customColor);
        },

        commitCustomColor() {
            this.update(this.customColor);
        },

        handleOkClick() {
            this.commitCustomColor();
            this.popoverOpen = false;
        },

        resetColor() {
            this.update(null);
        },

        sanitizeColor(color) {
            if (color && /^#?([a-fA-F0-9]{3,6})$/.test(color.trim())) {
                color = color.replace(/[^a-fA-F0-9]/g, '');
                if (color.length === 3) {
                    color = color[0] + color[0] + color[1] + color[1] + color[2] + color[2];
                }
                return `#${color}`;
            }
        },
    },
};
</script>
