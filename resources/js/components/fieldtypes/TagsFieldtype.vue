<template>
    <div class="tag-fieldtype-wrapper">
        <input type="text" v-el:tags :value="data" />
    </div>
</template>

<script>
module.exports = {

    mixins: [Fieldtype],

    ready: function () {
        var self = this;

        $(this.$els.tags).selectize({
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
            this.$els.tags.selectize.focus();
        }

    }
};
</script>
