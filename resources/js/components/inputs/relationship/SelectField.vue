<template>

    <div>
        <v-select
            ref="input"
            label="title"
            :close-on-select="true"
            :disabled="readOnly"
            :multiple="multiple"
            :options="options"
            :placeholder="config.placeholder || __('Choose...')"
            :searchable="true"
            :taggable="taggable"
            :value="items"
            @input="input"
            @search="search"
        />
    </div>

</template>

<script>
export default {

    props: {
        items: Array,
        url: String,
        typeahead: Boolean,
        multiple: Boolean,
        taggable: Boolean,
        config: Object,
        readOnly: Boolean
    },

    data() {
        return {
            options: [],
        }
    },

    created() {
        if (! this.typeahead) {
            // Get the items via ajax.
            // TODO: To save on requests, this should probably be done in the preload step and sent via meta.
            this.$axios.get(this.url, { params: {}}).then(response => {
                this.options = response.data.data;
            });
        }
    },

    methods: {

        search(search, loading) {
            if (! this.typeahead) return;

            loading(true);

            this.$axios.get(this.url, { params: { search }}).then(response => {
                loading(false);
                this.options = response.data.data;
            });
        },

        input(items) {
            if (! this.multiple) {
                items = items === null ? [] : [items];
            }

            // On-the-fly items created (when using taggable) won't have an ID.
            // But v-select gives them a title, so we'll use that as the ID.
            items = items.map(item => {
                if (! item.id) item.id = item.title;
                return item;
            });

            this.$emit('input', items);
        }

    }

}
</script>
