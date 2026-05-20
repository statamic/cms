<template>
    <div class="asset-folder-fieldtype-wrapper">
        <relationship-fieldtype
            v-if="container"
            :handle="handle"
            :value="value"
            :meta="relationshipMeta"
            :config="relationshipConfig"
            @input="update"
        />
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: {
        storeName: {
            default: null
        }
    },

    computed: {

        container() {
            let container = this.config.container;

            if (container) {
                return Array.isArray(container) ? container[0] : container;
            }

            if (! this.storeName) return null;

            const state = this.$store?.state?.publish?.[this.storeName];
            if (! state) return null;

            container = state.values.container;

            if (Array.isArray(container)) {
                return container[0] || null;
            }

            return container || null;
        },

        relationshipConfig() {
            return {
                ...this.config,
                type: 'asset_folder',
            };
        },

        relationshipMeta() {
            return {...this.meta, ...{
                getBaseSelectionsUrlParameters: { container: this.container }
            }};
        }

    }

};
</script>
