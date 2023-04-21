<template>

    <v-date-picker
        v-bind="pickerBindings"
        @input="$emit('input', $event)"
    />

</template>

<script>
import Picker from './Picker';

export default {

    mixins: [Picker],

    computed: {

        pickerBindings() {
            return {
                ...this.bindings,
                isRange: true,
                disabledDates: this.isReadOnly ? { weekdays: [1, 2, 3, 4, 5, 6, 7] } : null
            }
        },

    }

}
</script>
