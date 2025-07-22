<template>
    <div class="template-folder-fieldtype-wrapper">
        <relationship-fieldtype
            :handle="handle"
            :value="value"
            :meta="relationshipMeta"
            :config="{ type: 'template_folder', mode: 'select', max_items: 1 }"
            @input="update"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

    computed: {
        relationshipMeta() {
            return {
                ...this.meta,
                ...{
                    getBaseSelectionsUrlParameters: {},
                },
            };
        },
    },
};
</script>
