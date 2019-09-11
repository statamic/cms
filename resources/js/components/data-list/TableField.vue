<template>
    <div><component :is="component" :field="field" :value="value" :values="values" /></div>
</template>

<script>
import DefaultField from './DefaultField.vue';

export default {

    components: { DefaultField },

    props: {
        field: { required: true },
        value: { required: true },
        values: { required: true },
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
