export default {

    computed: {

        /**
         * Allows "suggest-ish" fields (Suggest, Relate, etc) to work out the key used to get prefetched
         * suggestions generated on the server side and inserted into Statamic.Publish.suggestions.
         * This will prevent an AJAX request for every instance of this fieldtype.
         */
        suggestKey() {
            let config = _.omit(this.config, [
                'display', 'instructions', 'max_items', 'localizable', 'required', 'name', 'placeholder'
            ]);

            return JSON.stringify(config);
        }

    }
};
