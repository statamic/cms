<template>
    <div class="asset-folder-fieldtype-wrapper">
        <small class="help-block" v-if="!container">{{ __('Select asset container') }}</small>

        <relationship-fieldtype
            v-if="container"
            :name="name"
            :value="value"
            :meta="relationshipMeta"
            :config="{ type: 'asset_folder' }"
            @updated="update($event)"
        />
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    computed: {

        container() {
            return this.$store.state.publish[this.storeName].values.container[0];
        },

        relationshipMeta() {
            return {...this.meta, ...{
                getBaseSelectionsUrlParameters: { container: this.container }
            }};
        }

    }

};
</script>
