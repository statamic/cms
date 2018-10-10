<template>
    <div class="suggest-fieldtype-wrapper">
        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>

        <select v-if="!loading"
                ref="select"
                :name="name"
                :placeholder="translate('cp.please_select')"
                :multiple="true">
        </select>
    </div>
</template>

<script>
import GetsSuggestKey from './GetsSuggestKey';

export default {

    mixins: [Fieldtype, GetsSuggestKey],

    props: ['suggestionsProp'],

    data: function() {
        return {
            loading: true,
            suggestions: []
        }
    },

    watch: {

        value(value) {
            this.$refs.select.selectize.setValue(value);
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

            if (this.value) {
                var formatted = [];
                _.each(this.data, function(value, key, list) {
                    formatted.push({'value': value, 'text': value});
                });
                this.suggestions = _.union(suggestions, formatted);
            }

            this.loading = false;

            this.$nextTick(function() {
                this.initSelectize();
            });
        },

        initSelectize: function() {
            var self = this;

            let opts = {
                options: this.suggestions,
                items: this.data,
                create: this.config.create || false,
                maxItems: this.config.max_items,
                placeholder: this.config.placeholder,
                plugins: ['drag_drop', 'remove_button'],
                onChange: function(value) {
                    self.update(value);
                }
            };

            const optgroups = _.chain(this.suggestions).pluck('optgroup').unique().filter().map(optgroup => {
                return { value: optgroup, label: optgroup };
            }).value();

            if (optgroups.length) {
                opts.optgroups = optgroups;
            }

            $(this.$refs.select).selectize(opts);
        },

        getReplicatorPreviewText() {
            if (! this.value) return;

            let values = JSON.parse(JSON.stringify(this.value));

            if (this.suggestions) {
                values = values.map(value => {
                    const suggestion = _.findWhere(this.suggestions, { value });
                    return suggestion ? suggestion.text : value;
                });
            }

            return values.join(', ');
        }

    },

    mounted() {
        this.getSuggestions();
    }
};
</script>
