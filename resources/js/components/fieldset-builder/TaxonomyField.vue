<template>

    <tr>
        <td>{{ taxonomy.title }}</td>
        <td><code>{{ taxonomy.id }}</code></td>
        <td>{{ label }}</td>
        <td class="row-controls">

            <toggle-fieldtype :data.sync="field.hidden" :config="{ reverse: true }"></toggle-fieldtype>

            <span class="icon icon-edit edit" @click="select"></span>
            <span class="icon icon-menu move drag-handle"></span>


            <modal :show.sync="editing" class="markdown-modal">
                <template slot="header">{{ taxonomy.title }}</template>
                <template slot="body">
                    <field-settings :field.sync="field"
                                    :fieldtype-config="fieldtypeConfig"
                                    :is-taxonomy="true">
                    </field-settings>
                </template>
            </modal>

        </td>
    </tr>

</template>


<script>
export default {

    props: ['taxonomy', 'field', 'fieldtypeConfig'],


    data() {
        return {
            editing: false
        }
    },


    computed: {

        handle() {
            return this.taxonomy.id;
        },

        hidden() {
            return this.field.hidden === true;
        },

        visible() {
            return !this.hidden;
        },

        label() {
            return this.field.display || this.taxonomy.title;
        }

    },


    methods: {

        select() {
            this.field.hidden = false;
            this.editing = true;
        }

    }

}
</script>
