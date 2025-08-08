<template>
    <div class="asset-folder-fieldtype-wrapper">
        <relationship-fieldtype
            v-if="container"
            :handle="handle"
            :value="value"
            :meta="relationshipMeta"
            :config="{ type: 'asset_folder', max_items: this.config.max_items }"
            @input="update"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

    computed: {
        container() {
            return this.publishContainer.values?.container[0] ?? this.config.container;
        },

        relationshipMeta() {
            return {
                ...this.meta,
                ...{
                    getBaseSelectionsUrlParameters: { container: this.container },
                },
            };
        },
    },
};
</script>
