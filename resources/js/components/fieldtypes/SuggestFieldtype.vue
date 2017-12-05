<template>
    <div class="suggest-fieldtype-wrapper">
        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>

        <select v-if="!loading"
                :name="name"
                :placeholder="translate('please_select')"
                :multiple="true">
        </select>
    </div>
</template>

<script>
import GetsSuggestKey from './GetsSuggestKey';

module.exports = {

    mixins: [Fieldtype, GetsSuggestKey],

    props: ['suggestionsProp'],

    data: function() {
        return {
            loading: true,
            suggestions: []
        }
    },

    methods: {

        getSuggestions: function() {
            if (this.suggestionsProp) {
                this.populateSuggestions(this.suggestionsProp);
            } else {
                const prefetched = data_get(Statamic, 'Publish.suggestions.' + this.suggestKey);
                if (prefetched) {
                    this.populateSuggestions(prefetched);
                } else {
                    this.$http.post(cp_url('addons/suggest/suggestions'), this.config, function(data) {
                        this.populateSuggestions(data);
                    });
                }
            }
        },

        populateSuggestions(suggestions) {
            this.suggestions = suggestions;
            this.loading = false;
            this.$nextTick(function() {
                this.initSelectize();
            });
        },

        initSelectize: function() {
            var self = this;

            $(this.$el).find('select').selectize({
                options: this.suggestions,
                items: this.data,
                create: this.config.create || false,
                maxItems: this.config.max_items,
                placeholder: this.config.placeholder,
                plugins: ['drag_drop', 'remove_button'],
                onChange: function(value) {
                    self.data = value;
                }
            });
        },

        getReplicatorPreviewText() {
            if (! this.data) return;

            let values = JSON.parse(JSON.stringify(this.data));

            if (this.suggestions) {
                values = values.map(value => {
                    const suggestion = _.findWhere(this.suggestions, { value });
                    return suggestion ? suggestion.text : value;
                });
            }

            return values.join(', ');
        }

    },

    ready: function() {
        this.getSuggestions();
    }
};
</script>
