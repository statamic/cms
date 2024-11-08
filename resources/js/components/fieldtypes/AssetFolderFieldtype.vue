<template>
    <div class="asset-folder-fieldtype-wrapper">
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
