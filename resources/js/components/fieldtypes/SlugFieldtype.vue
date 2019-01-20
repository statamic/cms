<template>

    <text-fieldtype
        class="font-mono text-xs"
        name="slug"
        :config="config"
        :value="value"
        @updated="update" />

</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype, AutoSlug],

    created() {
        this.autoSlug('title');
    }

}
</script>
