<template>
    <div><component :is="component" :value="value" /></div>
</template>

<script>
import DefaultField from './DefaultField.vue';

export default {

    components: { DefaultField },

    props: {
        value: { required: true },
        fieldtype: String
    },

    computed: {

        component() {
            const fallback = 'DefaultField';
            const custom = `${this.fieldtype}-fieldtype-index`;

            if (!this.value || !this.fieldtype) return fallback;

            return Vue.options.components[custom] ? custom : fallback;
        }

    }

}
</script>
