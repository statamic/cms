<template>
    <div class=" flex items-center">
        <div class="input-group w-auto" :class="{ 'max-w-[130px]': config.allow_any }">
            <popover name="swatches" class="color-picker" placement="bottom-start">
                <template #trigger>
                    <div
                        class="input-group-prepend px-px"
                        v-tooltip="__('Pick Color')"
                    >
                        <div class="relative flex items-center outline-none">
                            <div class="inline-block cursor-pointer rounded m-0 p-[2px]">
                                <div
                                    class="rounded-sm w-8 h-8"
                                    :class="{ 'border dark:border-dark-900': !value, 'cursor-not-allowed': isReadOnly }"
                                    :style="{ 'background-color': value }"
                                />
                            </div>
                        </div>
                    </div>
                </template>
                <template #default="{ close: closePopover }">
                    <div class="p-4">
                        <div v-if="config.swatches.length" class="grid grid-cols-4 gap-3">
                            <div
                                v-for="swatch in config.swatches"
                                class="w-10 h-10 inline-block cursor-pointer rounded flex border border-gray-400"
                                :style="{ 'background-color': swatch }"
                                @click="() => { update(swatch); closePopover(); }"
                            >
                                <div
                                    v-if="swatch === value"
                                    class="flex items-center justify-center h-full w-full"
                                >
                                    <div class="w-5 h-5 rounded-full bg-black/10 flex items-center justify-center">
                                        <svg version="1.1" role="presentation" width="12" height="12" viewBox="0 0 1792 1792" class="text-white fill-current"><path d="M1671 566q0 40-28 68l-724 724-136 136q-28 28-68 28t-68-28l-136-136-362-362q-28-28-28-68t28-68l136-136q28-28 68-28t68 28l294 295 656-657q28-28 68-28t68 28l136 136q28 28 28 68z" ></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="config.allow_any"
                            class="flex items-center"
                            :class="{ 'mt-5': config.swatches.length }"
                        >
                            <input
                                class="h-10 w-full cursor-pointer input-text w-full rounded p-[2px] rtl:ml-2 ltr:mr-2"
                                type="color"
                                :value="customColor"
                                @input="customColorSelected"
                            />
                            <button
                                class="btn btn-primary h-10 px-2"
                                v-text="__('OK')"
                                @click="() => { commitCustomColor(); closePopover() }"
                            />
                        </div>
                    </div>
                </template>
            </popover>

            <input
                v-if="config.allow_any"
                class="input-text font-mono"
                maxlength="7"
                type="text"
                :readonly="isReadOnly"
                :value="customColor"
                @input="updateDebounced($event.target.value)"
                @blur="sanitizeCustomColor"
            />
        </div>

        <button v-if="value" class="btn-close rtl:mr-1 ltr:ml-1" :aria-label="__('Reset')" @click="resetColor">&times;</button>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
export default {

    mixins: [Fieldtype],

    data () {
        return {
            customColor: this.value
        }
    },

    watch: {

        value(value) {
            this.customColor = value;
        },

    },

    computed: {

        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

            return this.value
                ? replicatorPreviewHtml(`<span class="little-dot" style="background-color:${this.value}"></span>`)
                : null;
        }

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
        }
    }

};
</script>
