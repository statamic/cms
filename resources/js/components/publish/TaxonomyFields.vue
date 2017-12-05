<template>

        <div class="form-group full-width" v-for="config in taxonomies">
            <label class="block">{{ config.display || config.title }}</label>
            <small class="help-block" v-if="config.instructions" v-html="config.instructions | markdown"></small>
            <taxonomy-fieldtype :config="config" :data.sync="fields[config.taxonomy]" :leave-alert="true"></taxonomy-fieldtype>
        </div>

</template>


<script>
export default {

    props: ['taxonomies', 'fields']

}
</script>
