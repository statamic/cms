<template>
    <div class="relative w-full">
        <v-date-picker
            v-bind="pickerBindings"
            @update:model-value="$emit('update:model-value', $event)"
            :is-dark="darkMode"
        />
        <div class="absolute inset-0 z-1 cursor-not-allowed" v-if="isReadOnly" />
    </div>
</template>

<script>
import Picker from './Picker';

export default {
    emits: ['update:model-value'],

    mixins: [Picker],

    computed: {
        darkMode() {
            return Statamic.darkMode;
        },

        pickerBindings() {
            return {
                ...this.bindings,
                disabledDates: this.isReadOnly ? { weekdays: [1, 2, 3, 4, 5, 6, 7] } : null,
            };
        },
    },
};
</script>
