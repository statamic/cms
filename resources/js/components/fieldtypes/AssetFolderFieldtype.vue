<template>
    <div class="asset-folder-fieldtype-wrapper">
        <small class="help-block text-gray-600" v-if="!container">{{ __('Select asset container') }}</small>

        <relationship-fieldtype
            v-if="container"
            :handle="handle"
            :meta="relationshipMeta"
            :config="{ type: 'asset_folder' }"
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
            return data_get(this.$store.state.publish[this.storeName].values.container, '0', null);
        },
        relationshipMeta() {
            return {...this.meta, ...{
                getBaseSelectionsUrlParameters: { container: this.container }
            }};
        }
    }
};
</script>
