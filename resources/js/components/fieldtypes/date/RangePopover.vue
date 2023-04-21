<template>

    <div class="w-full" v-on-clickaway="clickaway">

        <v-portal :disabled="!open" :to="portalTarget">
            <v-date-picker
                ref="picker"
                v-bind="pickerBindings"
                v-show="open"
                @input="dateSelected"
            />
        </v-portal>

        <div
            class="w-full flex items-start @md:items-center flex-col @md:flex-row"
        >

            <div class="input-group">
                <popover
                    :offset="[5, 0]"
                    :clickaway="false"
                    ref="startPopover"
                    placement="bottom-start"
                    :disabled="isReadOnly"
                    @opened="startPopoverOpened"
                    @closed="startPopoverClosed"
                >
                    <template #trigger>
                        <div class="input-group-prepend flex items-center">
                            <svg-icon name="light/calendar" class="w-4 h-4" />
                        </div>
                    </template>
                    <portal-target :name="startPortalTarget" />
                </popover>
                <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                    <input
                        class="input-text-minimal p-0 bg-transparent leading-none"
                        :readonly="isReadOnly"
                        :value="startInputValue"
                        v-on="startInputEvents"
                        @focus="startInputFocused"
                        @blur="$emit('blur')"
                    />
                </div>
            </div>

            <svg-icon name="micro/arrow-right" class="w-6 h-6 my-1 mx-2 text-gray-700 hidden @md:block" />
            <svg-icon name="micro/arrow-right" class="w-3.5 h-3.5 my-2 mx-2.5 rotate-90 text-gray-700 @md:hidden" />


            <div class="input-group">
                <popover
                    :offset="[5, 0]"
                    :clickaway="false"
                    ref="endPopover"
                    placement="bottom-start"
                    :disabled="isReadOnly"
                    @opened="endPopoverOpened"
                    @closed="endPopoverClosed"
                >
                    <template #trigger>
                        <div class="input-group-prepend flex items-center">
                            <svg-icon name="light/calendar" class="w-4 h-4" />
                        </div>
                    </template>
                    <portal-target :name="endPortalTarget" />
                </popover>
                <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                    <input
                        class="input-text-minimal p-0 bg-transparent leading-none"
                        :readonly="isReadOnly"
                        :value="endInputValue"
                        v-on="endInputEvents"
                        @focus="endInputFocused"
                        @blur="$emit('blur')"
                    />
                </div>
            </div>

        </div>

    </div>

</template>

<script>
import Picker from './Picker';
import { mixin as clickaway } from 'vue-clickaway';

export default {

    mixins: [Picker, clickaway],

    data() {
        return {
            startOpen: false,
            endOpen: false,
            picker: null,
            portalTarget: null,
            startPortalTarget: `date-picker-start-${this._uid}`,
            endPortalTarget: `date-picker-end-${this._uid}`,
            startInputValue: null,
            endInputValue: null,
        }
    },

    computed: {

        pickerBindings() {
            return {
                ...this.bindings,
                isRange: true,
                disabledDates: this.isReadOnly ? { weekdays: [1, 2, 3, 4, 5, 6, 7] } : null
            }
        },

        open() {
            return this.startOpen || this.endOpen;
        },

        startInputEvents() {
            return {
                // Handle changing the date when typing.
                change: (e) => this.picker.onInputUpdate(e.target.value, true, { formatInput: true }),
                // Allows hitting escape to cancel any changes.
                keyup: (e) => this.picker.onInputKeyup(e),
            };
        },

        endInputEvents() {
            return {
                // Handle changing the date when typing.
                change: (e) => this.picker.onInputUpdate(e.target.value, false, { formatInput: true }),
                // Allows hitting escape to cancel any changes.
                keyup: (e) => this.picker.onInputKeyup(e),
            };
        }

    },

    watch: {

        'bindings.value': function () {
            this.$nextTick(() => this.updateInputValues());
        },

    },

    mounted() {
        this.$nextTick(() => {
            this.resetPicker();
            this.updateInputValues();
        });
    },

    methods: {

        startPopoverOpened() {
            if (this.endOpen) this.$refs.endPopover.close();

            this.startOpen = true;
            this.portalTarget = this.startPortalTarget;
            this.$nextTick(() => this.resetPicker());
        },

        startPopoverClosed() {
            this.startOpen = false;
            this.portalTarget = null;
            this.$nextTick(() => this.resetPicker());
        },

        endPopoverOpened() {
            if (this.startOpen) this.$refs.startPopover.close();

            this.endOpen = true;
            this.portalTarget = this.endPortalTarget;
            this.$nextTick(() => this.resetPicker());
        },

        endPopoverClosed() {
            this.endOpen = false;
            this.portalTarget = null;
            this.$nextTick(() => this.resetPicker());
        },

        updateInputValues() {
            this.startInputValue = this.picker.inputValues[0];
            this.endInputValue = this.picker.inputValues[1];
        },

        dateSelected(date) {
            this.$emit('input', date)
            this.$nextTick(() => {
                this.$refs.startPopover.close()
                this.$refs.endPopover.close()
            });
        },

        resetPicker() {
            this.picker = this.$refs.picker;
        },

        startInputFocused(e) {
            this.$refs.startPopover.open();
            this.$emit('focus', e.target)
        },

        endInputFocused(e) {
            this.$refs.endPopover.open();
            this.$emit('focus', e.target)
        },

        clickaway(e) {
            if (this.picker.$el.contains(e.target) || this.$el.contains(e.target)) return;

            this.$refs.startPopover.close();
            this.$refs.endPopover.close();
        }

    }

}
</script>
