<template>

    <div class="w-full" v-on-clickaway="clickaway">

        <v-portal :disabled="!open" :to="portalTarget">
            <v-date-picker
                ref="picker"
                v-bind="bindings"
                v-show="open"
                @input="dateSelected"
            />
        </v-portal>

        <div class="input-group">
            <popover
                :offset="[-3, 0]"
                :clickaway="false"
                ref="popover"
                placement="bottom"
                :disabled="isReadOnly"
                @opened="popoverStateChanged(true)"
                @closed="popoverStateChanged(false)"
            >
                <template #trigger>
                    <div class="input-group-prepend flex items-center">
                        <svg-icon name="light/calendar" class="w-4 h-4" />
                    </div>
                </template>
                <portal-target :name="portalTarget" />
            </popover>
            <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                <input
                    class="input-text-minimal p-0 bg-transparent leading-none"
                    :readonly="isReadOnly"
                    :value="inputValue"
                    v-on="inputEvents"
                    @focus="inputFocused"
                    @blur="$emit('blur')"
                />
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
            open: false,
            picker: null,
            portalTarget: `date-picker-${this._uid}`,
            inputValue: null
        }
    },

    computed: {

        inputEvents() {
            return {
                // Handle changing the date when typing.
                change: (e) => this.picker.onInputUpdate(e.target.value, true, { formatInput: true }),
                // Allows hitting escape to cancel any changes.
                keyup: (e) => this.picker.onInputKeyup(e),
            }
        },

    },

    watch: {

        'bindings.value': function () {
            this.$nextTick(() => this.updateInputValue());
        },

    },

    methods: {

        popoverStateChanged(open) {
            this.open = open;
            this.$nextTick(() => this.resetPicker());
        },

        updateInputValue() {
            this.inputValue = this.picker.inputValues[0];
        },

        dateSelected(date) {
            this.$emit('input', date)
            this.$nextTick(() => this.$refs.popover?.close());
        },

        resetPicker() {
            this.picker = this.$refs.picker;
        },

        inputFocused(e) {
            this.$refs.popover.open();
            this.$emit('focus', e.target);
        },

        clickaway(e) {
            if (this.picker.$el.contains(e.target) || this.$el.contains(e.target)) return;

            this.$refs.popover.close();
        }

    },

    mounted() {
        this.$nextTick(() => {
            this.resetPicker();
            this.updateInputValue();
        });
    }

}
</script>
