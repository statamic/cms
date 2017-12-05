<template>

    <select v-el:tags multiple tabindex="0" ></select>

    <input type="hidden" :name="name" :value="data|json" class="form-control" />

</template>

<script>
export default {

    props: [
        'data',
        'suggestions',
        'maxItems',
        'create',
        'name'
    ],


    ready() {

        let self = this;

        // Ensure we have an array
        if (typeof this.data === 'string') {
            this.data = [this.data];
        }

        $(this.$els.tags).selectize({
            options: this.suggestions,
            items: this.data,
            maxItems: this.maxItems,
            plugins: ['drag_drop', 'remove_button'],
            onChange: function(value) {
                self.data = value;
            },
            create: this.create
        });

    },


    methods: {

        focus() {
            this.$els.tags.selectize.focus();
        }

    }

}
</script>
