<template>
    <div class="template-folder-fieldtype-wrapper">
        <relationship-fieldtype
            :handle="handle"
            :value="value"
            :meta="relationshipMeta"
            :config="{ type: 'template_folder' }"
            @input="update"
        />
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    computed: {

        relationshipMeta() {
            return {...this.meta, ...{
                getBaseSelectionsUrlParameters: { }
            }};
        }

    }

};
</script>
