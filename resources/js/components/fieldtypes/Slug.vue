<template>

    <text-fieldtype
        class="font-mono text-xs"
        :config="config"
        :initial-value="initialValue"
        @updated="update" />

</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
