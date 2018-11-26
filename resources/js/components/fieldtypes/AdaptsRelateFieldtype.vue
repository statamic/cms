<template>

    <div>
        <relate-fieldtype
            ref="relate"
            :value="value"
            :name="name"
            :config="config">
        </relate-fieldtype>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

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
