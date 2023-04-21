<template>

    <div class="w-full">

        <v-portal :disabled="!open" :to="portalTarget">
            <v-date-picker
                ref="picker"
                v-bind="{...bindings, isRange: true}"
                v-show="open"
                @input="dateSelected"
            />
        </v-portal>

        <div
            class="w-full flex items-start @md:items-center flex-col @md:flex-row"
        >

            <popover
                ref="startPopover"
                placement="bottom-start"
                class="w-full"
                @opened="startPopoverOpened"
                @closed="startPopoverClosed"
            >
                <template #trigger>
                    <div class="input-group">
                        <div class="input-group-prepend flex items-center">
                            <svg-icon name="light/calendar" class="w-4 h-4" />
                        </div>
                        <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                            <input
                                class="input-text-minimal p-0 bg-transparent leading-none"
                                :value="startInputValue"
                                v-on="startInputEvents"
                            />
                                <!-- :value="inputValue.start"
                                :readonly="isReadOnly"
                                @focus="focusedField = $event.target"
                                @blur="focusedField = null"
                                v-on="!isReadOnly && inputEvents.start" -->
                        </div>
                    </div>
                </template>
                <portal-target :name="startPortalTarget" />
            </popover>

            <svg-icon name="micro/arrow-right" class="w-6 h-6 my-1 mx-2 text-gray-700 hidden @md:block" />
            <svg-icon name="micro/arrow-right" class="w-3.5 h-3.5 my-2 mx-2.5 rotate-90 text-gray-700 @md:hidden" />


            <popover
                ref="endPopover"
                placement="bottom-start"
                class="w-full"
                @opened="endPopoverOpened"
                @closed="endPopoverClosed"
            >
                <template #trigger>
                    <div class="input-group">
                        <div class="input-group-prepend flex items-center">
                            <svg-icon name="light/calendar" class="w-4 h-4" />
                        </div>
                        <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                            <input
                                class="input-text-minimal p-0 bg-transparent leading-none"
                                :value="endInputValue"
                                v-on="endInputEvents"
                            />
                                <!-- :value="inputValue.end"
                                :readonly="isReadOnly"
                                @focus="focusedField = $event.target"
                                @blur="focusedField = null"
                                v-on="!isReadOnly && inputEvents.end" -->
                        </div>
                    </div>
                </template>
                <portal-target :name="endPortalTarget" />
            </popover>

        </div>

    </div>

</template>

<script>
import Picker from './Picker';

export default {

    mixins: [Picker],

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
        }

    }

}
</script>
