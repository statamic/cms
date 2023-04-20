<template>

    <div>

        <v-date-picker
            ref="picker"
            v-bind="{...bindings, isRange: true}"
            @input="$emit('input', $event)"
        />


        <div
            class="w-full flex items-start @md:items-center flex-col @md:flex-row"
        >
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

            <svg-icon name="micro/arrow-right" class="w-6 h-6 my-1 mx-2 text-gray-700 hidden @md:block" />
            <svg-icon name="micro/arrow-right" class="w-3.5 h-3.5 my-2 mx-2.5 rotate-90 text-gray-700 @md:hidden" />

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
        </div>

    </div>

</template>

<script>
import Picker from './Picker';

export default {

    mixins: [Picker],

    data() {
        return {
            mounted: false,
        }
    },

    computed: {

        startInputValue() {
            return this.mounted ? this.$refs.picker.inputValues[0] : null;
        },

        endInputValue() {
            return this.mounted ? this.$refs.picker.inputValues[1] : null;
        },

        startInputEvents() {
            if (!this.mounted) return;

            return {
                // Handle changing the date when typing.
                change: (e) => this.$refs.picker.onInputUpdate(e.target.value, true, { formatInput: true }),
                // Allows hitting escape to cancel any changes.
                keyup: (e) => this.$refs.picker.onInputKeyup(e),
            };
        },

        endInputEvents() {
            if (!this.mounted) return;

            return {
                // Handle changing the date when typing.
                change: (e) => this.$refs.picker.onInputUpdate(e.target.value, false, { formatInput: true }),
                // Allows hitting escape to cancel any changes.
                keyup: (e) => this.$refs.picker.onInputKeyup(e),
            };
        }

    },

    mounted() {
        this.mounted = true;
    }

}
</script>
