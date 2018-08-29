<template>
    <toggle-input :value="value" @input="toggled" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype],

    data() {
        return {
            value: this.initialValue || false
        }
    },

    methods: {

        toggled(value) {
            this.value = value;
            this.update(this.value);
        }

    }

}
</script>
