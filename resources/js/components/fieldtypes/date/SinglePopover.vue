<template>

    <div class="w-full">

        <v-portal :disabled="!open" :to="portalTarget">
            <v-date-picker
                ref="picker"
                v-bind="bindings"
                v-show="open"
                @input="dateSelected"
            />
        </v-portal>

        <popover
            ref="popover"
            placement="bottom-start"
            :disabled="isReadOnly"
            @opened="open = true"
            @closed="open = false"
        >
            <template #trigger>
                <div class="input-group">
                    <div class="input-group-prepend flex items-center">
                        <svg-icon name="light/calendar" class="w-4 h-4" />
                    </div>
                    <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                        <input
                            class="input-text-minimal p-0 bg-transparent leading-none"
                            :readonly="isReadOnly"
                            :value="inputValue"
                            v-on="inputEvents"
                            @focus="$emit('focus', $event.target)"
                            @blur="$emit('blur')"
                        />
                    </div>
                </div>
            </template>
            <portal-target :name="portalTarget" @change="resetPicker" />
        </popover>

    </div>

</template>

<script>
import Picker from './Picker';

export default {

    mixins: [Picker],

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
                // Allows hitting escape to cancel any changes, and close the popover.
                keyup: (e) => {
                    this.picker.onInputKeyup(e);
                    if (e.key === 'Escape') this.$refs.popover?.close();
                }
            }
        },

    },

    watch: {

        'bindings.value': function () {
            this.$nextTick(() => this.updateInputValue());
        },

    },

    methods: {

        updateInputValue() {
            this.inputValue = this.picker.inputValues[0];
        },

        dateSelected(date) {
            this.$emit('input', date)
            this.$nextTick(() => this.$refs.popover?.close());
        },

        resetPicker() {
            this.picker = this.$refs.picker;
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
