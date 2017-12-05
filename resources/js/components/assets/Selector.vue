<template>
    <div class="asset-selector-modal">

        <div class="asset-selector">

            <asset-browser
                :selected-container="container"
                :selected-path="folder"
                :selected-assets="browserSelections"
                :restrict-navigation="restrictNavigation"
                :max-files="maxFiles"
                @selections-updated="selectionsUpdated"
                @asset-doubleclicked="select">

                <template slot="contextual-actions" v-if="browserSelections.length">
                    <button class="btn action mb-24" @click="browserSelections = []">{{ translate('cp.uncheck_all') }}</button>
                </template>

            </asset-browser>

            <div class="modal-footer">
                <div class="left" v-if="browserSelections.length">
                    {{ browserSelections.length }}<span v-if="maxFiles">/{{ maxFiles }}</span> {{ translate('cp.selected') }}
                </div>
                <button
                    type="button"
                    class="btn"
                    @click="close">
                    {{ translate('cp.cancel') }}
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    @click="select">
                    {{ translate('cp.select') }}
                </button>
            </div>

        </div>

    </div>
</template>

<script>
module.exports = {
    props: {
        container: String,
        folder: String,
        selected: Array,
        maxFiles: Number,
        restrictNavigation: {
            type: Boolean,
            default() {
                return false;
            }
        }
    },


    data() {
        return {
            // We will initialize the browser component with the selections, but not pass in the selections directly.
            // We only want selection changes to be reflected in the fieldtype once the user is ready to commit
            // them. They should be able to cancel at any time and have their updated selections discarded.
            browserSelections: this.selected
        }
    },


    methods: {

        /**
         * Confirm the updated selections
         */
        select: function() {
            this.$emit('selected', this.browserSelections);
            this.close();
        },

        /**
         * Close this selector
         */
        close() {
            this.$emit('closed');
        },

        /**
         * Selections have been updated within the browser component.
         */
        selectionsUpdated(selections) {
            this.browserSelections = selections;
        }

    }

};
</script>
