<template>
    <div><component :class="cssClass" :is="component" :handle="handle" :value="value" :values="values" /></div>
</template>

<script>
import DefaultField from './DefaultField.vue';

export default {

    components: { DefaultField },

    props: {
        handle: { type: String, required: true },
        value: { required: true },
        values: { required: true },
        fieldtype: String
    },

    computed: {

        component() {
            const fallback = 'DefaultField';
            const custom = `${this.fieldtype}-fieldtype-index`;

            if (!this.fieldtype) return fallback;

            return Vue.options.components[custom] ? custom : fallback;
        },

        cssClass() {
            return this.fieldtype ? `${this.fieldtype}-index-field` : '';
        }

    }

}
</script>
