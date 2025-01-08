<template>
    <div class="asset-folder-fieldtype-wrapper">
        <relationship-fieldtype
            v-if="container"
            :handle="handle"
            :meta="relationshipMeta"
            :config="{ type: 'asset_folder', max_items: this.config.max_items }"
            :model-value="modelValue"
            @update:model-value="update"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue'

export default {
    mixins: [Fieldtype],

    inject: ['storeName'],

    computed: {
        container() {
            return data_get(this.$store.state.publish[this.storeName].values.container, '0', this.config.container);
        },
        relationshipMeta() {
            return {...this.meta, ...{
                getBaseSelectionsUrlParameters: { container: this.container }
            }};
        }
    }
};
</script>
