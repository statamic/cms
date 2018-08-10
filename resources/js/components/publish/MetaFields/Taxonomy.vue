<template>

    <taxonomy-fieldtype
        name="categories"
        :config="fieldConfig"
        :data.sync="formData.fields[config.name]"
        :leave-alert="true"
    ></taxonomy-fieldtype>

</template>


<script>
import MetaFieldtype from './MetaFieldtype';

export default {

    mixins: [MetaFieldtype],

    computed: {

        fieldConfig() {
            return {
                type: 'taxonomy',
                taxonomy: this.config.name,
                width: this.config.width
            };
        }

    }

}
</script>

