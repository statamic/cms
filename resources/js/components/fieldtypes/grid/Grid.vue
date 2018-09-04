<template>

    <div>

        <table class="w-full mb-1">
            <grid-row
                v-for="(row, index) in rows"
                :key="`row-${index}`"
                :index="index"
                :fields="fields"
                :values="row"
                :name="name"
                @updated="updated"
            />
        </table>

        <button @click.prevent="addRow" class="btn">Add Row</button>

    </div>

</template>

<script>
import GridRow from './Row.vue';

export default {

    mixins: [Fieldtype],

    components: { GridRow },

    data() {
        return {
            rows: _.clone(this.value || [])
        }
    },

    computed: {

        fields() {
            return this.config.fields;
        }

    },

    methods: {

        addRow() {
            const row = _.chain(this.fields)
                .indexBy('handle')
                .mapObject(field => null)
                .value();

            this.rows.push(row);
        },

        updated(index, row) {
            this.rows.splice(index, 1, row);
        }

    },

    watch: {

        rows(rows) {
            this.$emit('updated', rows);
        }

    }

}
</script>
