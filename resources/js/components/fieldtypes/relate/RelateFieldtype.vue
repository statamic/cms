<template>
    <div class="relate-fieldtype">

        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>

        <relate-tags
            v-if="!loading && (tags || single)"
            v-ref:tags
            :data.sync="data"
            :suggestions="suggestions"
            :max-items="maxItems"
            :create="canCreate"
            :name="name">
        </relate-tags>

        <relate-panes
            v-if="!loading && panes && !single"
            v-ref:panes
            :data.sync="data"
            :suggestions="suggestions"
            :max-items="maxItems"
            :name="name">
        </relate-panes>

    </div>
</template>

<script>
import RelatePanes from './RelatePanesFieldtype.vue'
import RelateTags from './RelateTagsFieldtype.vue'
import GetsSuggestKey from '../GetsSuggestKey';

module.exports = {

    mixins: [Fieldtype, GetsSuggestKey],

    components: {
        'relate-panes': RelatePanes,
        'relate-tags': RelateTags
    },

    props: ['suggestionsProp'],

    data: function() {
        return {
            loading: true,
            suggestions: [],
            autoBindChangeWatcher: false,
            shouldFocusWhenLoaded: false
        }
    },

    computed: {

        single: function () {
            return this.maxItems && this.maxItems === 1;
        },

        maxItems: function() {
            return parseInt(this.config.max_items);
        },

        mode() {
            return this.config.mode || 'tags';
        },

        panes() {
            return this.mode === 'panes';
        },

        tags() {
            return this.mode === 'tags';
        },

        canCreate() {
            return this.config.create;
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
            this.removeInvalidData();
            this.loading = false;
            this.bindChangeWatcher();
            if (this.shouldFocusWhenLoaded) {
                this.$nextTick(() => this.focus());
            }
        },

        /**
         * Remove data that doesn't exist in the suggestions.
         *
         * These may be entries that have been deleted, for example.
         */
        removeInvalidData: function () {
            var self = this;

            if (self.single) {
                if (! _.findWhere(self.suggestions, { value: self.data[0] })) {
                    self.data = null;
                }
            } else {
                self.data = _.filter(self.data, function (item) {
                    return _.findWhere(self.suggestions, { value: item });
                });
            }
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
        },

        focus() {
            if (this.loading) {
                this.shouldFocusWhenLoaded = true;
                return;
            }

            this.$refs[this.mode].focus();
            this.shouldFocusWhenLoaded = false;
        }

    },

    ready: function() {
        if (!this.data) {
            this.data = [];
        }

        if (!this.config) {
            this.config = [];
        }

        this.getSuggestions();

        this.$watch('suggestionsProp', function(suggestions) {
            this.suggestions = suggestions;
        });
    }
};
</script>
