<template>

    <div class="form-group p-2 m-0" :class="`${field.type}-fieldtype`">

        <label class="block">{{ display }}</label>

        <div
            class="help-block"
            v-if="field.instructions"
            v-html="$options.filters.markdown(field.instructions)" />

        <component
            :is="fieldtypeComponent"
            :config="field"
            :value="value"
            :name="name"
            @updated="updated"
        />

    </div>

</template>

<script>
export default {

    props: {
        field: {
            type: Object,
            required: true
        },
        value: {
            required: true
        }
    },


    computed: {

        fieldtypeComponent() {
            return `${this.field.type}-fieldtype`;
        },

        name() {
            return 'todo';
        },

        display() {
            return this.field.display || this.field.handle[0].toUpperCase() + this.field.handle.slice(1)
        }

    },

    methods: {

        updated(value) {
            this.$emit('updated', this.field.handle, value);
        }

    }

}
</script>
