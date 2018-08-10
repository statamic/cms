<template>
    <div class="tag-fieldtype-wrapper">
        <input type="text" ref="tags" :value="data" />
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    mounted() {
        var self = this;

        $(this.$refs.tags).selectize({
            delimiter: ',',
            persist: false,
            plugins: ['drag_drop', 'remove_button'],
            create: function (input) {
                return {
                    value: input,
                    text: input
                }
            },
            onChange: function (value) {
                self.data = value.split(',');
            },
            onItemRemove: function (value) {
                delete this.options[value];
            }
        });
    },

    methods: {

        focus() {
            this.$refs.tags.selectize.focus();
        }

    }
};
</script>
