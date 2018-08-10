<template>
    <date-fieldtype
        name="date"
        :data.sync="formData.fields.date"
        :leave-alert="true"
        :config="config"
    ></date-fieldtype>
</template>


<script>
import MetaFieldtype from './MetaFieldtype';

export default {

    mixins: [MetaFieldtype],

    computed: {

        displayFallback() {
            return translate('cp.date_and_time');
        },

        isLocalizable() {
            return false;
        }

    }

}
</script>

