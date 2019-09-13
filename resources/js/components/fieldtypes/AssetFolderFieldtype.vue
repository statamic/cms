<template>
    <div class="asset-folder-fieldtype-wrapper">
        <small class="help-block text-grey-60" v-if="!container">{{ __('Select asset container') }}</small>

        <relationship-fieldtype
            v-if="container"
            :handle="handle"
            :value="value"
            :meta="relationshipMeta"
            :config="{ type: 'asset_folder' }"
            @input="update"
        />
    </div>
</template>

<script>
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
