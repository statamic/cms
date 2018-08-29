<template>
    <select-input :value="initialValue" :options="config.options" @input="update" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
