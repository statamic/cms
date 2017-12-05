<template>

    <div class="publish-fields">

        <div class="form-group">
            <label class="block">{{ translate('cp.fieldset') }}</label>
            <small class="help-block">{{ translate('cp.asset_fieldset_instructions') }}</small>
            <fieldset-fieldtype :data.sync="fieldset"></fieldset-fieldtype>
        </div>

        <div class="form-group">
            <button class="btn btn-default" @click="submit">Next Step</button>
        </div>

    </div>

</template>

<script>
    export default {

        props: ['fieldset'],


        methods: {

            submit() {
                this.$emit('submit');
            }

        }

    }
</script>
