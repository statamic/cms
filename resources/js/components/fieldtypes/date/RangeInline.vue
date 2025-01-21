<template>

    <div class="relative w-full">
        <v-date-picker
            v-bind="pickerBindings"
            @input="$emit('input', $event)"
            :is-dark="darkMode"
        />
        <div class="absolute inset-0 z-1 cursor-not-allowed" v-if="isReadOnly" />
    </div>

</template>

<script>
import Picker from './Picker';

export default {

    mixins: [Picker],

    computed: {

        darkMode() {
            return Statamic.darkMode;
        },

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
