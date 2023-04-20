<template>

    <div>

        <v-date-picker
            ref="picker"
            v-bind="bindings"
            @input="$emit('input', $event)"
        />

        <div class="input-group">
            <div class="input-group-prepend flex items-center">
                <svg-icon name="light/calendar" class="w-4 h-4" />
            </div>
            <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                <input
                    class="input-text-minimal p-0 bg-transparent leading-none"
                    :value="inputValue"
                    v-on="inputEvents"
                />
                    <!-- :value="inputValue"
                    :readonly="isReadOnly"
                    @focus="focusedField = $event.target"
                    @blur="focusedField = null"
                    v-on="!isReadOnly && inputEvents" -->
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

        inputValue() {
            return this.mounted ? this.$refs.picker.inputValues[0] : null;
        },

        inputEvents() {
            if (!this.mounted) return;

            return {
                // Handle changing the date when typing.
                change: (e) => this.$refs.picker.onInputUpdate(e.target.value, true, { formatInput: true }),
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
