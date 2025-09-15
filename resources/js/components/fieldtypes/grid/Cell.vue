<template>
    <td class="grid-cell" :class="classes" :width="width">
        <PublishField :config="field" />
    </td>
</template>

<script>
import { PublishField } from '@ui';

export default {
    components: {
        PublishField,
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
