<template>

    <input type="text" class="form-control type-text" autofocus="autofocus" v-model="formData.fields.title" />

</template>


<script>
import MetaFieldtype from './MetaFieldtype';

export default {

    mixins: [MetaFieldtype],

    computed: {

        displayFallback() {
            return translate('cp.title');
        },

        isRequired() {
            return true;
        },

        isLocalizable() {
            return true;
        }

    }

}
</script>

