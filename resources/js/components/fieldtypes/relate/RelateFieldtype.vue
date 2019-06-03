<template>
    <div class="relate-fieldtype">

        <loading-graphic v-if="loading" :size="16" :inline="true" />

        <relate-tags
            v-if="!loading && (tags || single)"
            ref="tags"
            :data="data"
            :suggestions="suggestions"
            :max-items="maxItems"
            :create="canCreate"
            :name="name"
            :disabled="disabled"
            @updated="update">
        </relate-tags>

        <relate-panes
            v-if="!loading && panes && !single"
            ref="panes"
            :value="data"
            :suggestions="suggestions"
            :max-items="maxItems"
            :name="name"
            @updated="update">
        </relate-panes>

    </div>
</template>

<script>
import RelatePanes from './RelatePanesFieldtype.vue'
import RelateTags from './RelateTagsFieldtype.vue'
import GetsSuggestKey from '../GetsSuggestKey';

export default {

    mixins: [Fieldtype, GetsSuggestKey],

    components: {
        RelatePanes,
        RelateTags
    },

    props: [
        'suggestionsProp',
        'disabled'
    ],

    data: function() {
        return {
            loading: true,
            data: null,
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
                    const type = this.config.type;
                    this.$axios.get(cp_url(`suggestions/${type}`), { params: this.config }).then(response => {
                        this.populateSuggestions(response.data);
                    });
                }
            }
        },

        populateSuggestions(suggestions) {
            this.suggestions = suggestions;
            this.removeInvalidData();
            this.loading = false;

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

    created() {
        this.data = this.value || [];

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
