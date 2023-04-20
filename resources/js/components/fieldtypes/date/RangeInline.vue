<template>

    <v-date-picker
        v-bind="{ ...bindings, isRange: true }"
        @input="$emit('input', $event)"
    />

</template>

<script>
import Picker from './Picker';

export default {

    mixins: [Picker],

}
</script>
