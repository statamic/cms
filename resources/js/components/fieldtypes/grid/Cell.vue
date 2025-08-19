<template>
    <td class="grid-cell" :class="classes" :width="width">
        <Field :config="field" />
    </td>
</template>

<script>
import Field from '@/components/ui/Publish/Field.vue';
export default {
    components: {
        Field,
    },

    props: {
        field: {
            type: Object,
            required: true,
        },
    },

    computed: {
        classes() {
            return [this.fieldtypeComponent, this.field.classes];
        },

        fieldtypeComponent() {
            return `${this.field.component || this.field.type}-fieldtype`;
        },

        width() {
            if (this.field.width) {
                return this.field.width + '%';
            }
        },
    },
};
</script>
