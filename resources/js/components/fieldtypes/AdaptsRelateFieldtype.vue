<template>

    <div>
        <relate-fieldtype
            v-ref:relate
            :data.sync="data"
            :name="name"
            :config="config">
        </relate-fieldtype>
    </div>

</template>

<script>
export default {

    props: ['data', 'config', 'name'],

    methods: {

        getReplicatorPreviewText() {
            if (! this.data) return;

            if (! this.$refs.relate) return this.data.join(', ');

            return this.$refs.relate.getReplicatorPreviewText();
        },

        focus() {
            this.$refs.relate.focus();
        }

    }

};
</script>
