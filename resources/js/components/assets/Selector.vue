<template>

<div>

        <modal
            name="asset-selector"
            width="90%"
            height="90%"
            :resizable="true"
        >
            <div class="flex flex-col justify-end h-full">

                <div class="flex-1 overflow-scroll">
                    <asset-browser
                        :initial-container="container"
                        :selected-path="folder"
                        :selected-assets="browserSelections"
                        :restrict-navigation="restrictNavigation"
                        :max-files="maxFiles"
                        @selections-updated="selectionsUpdated"
                        @asset-doubleclicked="select">

                        <template slot="contextual-actions" v-if="browserSelections.length">
                            <button class="btn action mb-3" @click="browserSelections = []">{{ translate('cp.uncheck_all') }}</button>
                        </template>

                    </asset-browser>
                </div>

                <div class="p-2 border-t flex items-center justify-between bg-grey-lightest">
                    <div class="text-sm text-grey-light">
                        {{ browserSelections.length }}<span v-if="maxFiles">/{{ maxFiles }}</span> {{ translate('cp.selected') }}
                    </div>
                    <div>
                        <button
                            type="button"
                            class="btn"
                            @click="close">
                            {{ translate('cp.cancel') }}
                        </button>

                        <button
                            type="button"
                            class="btn btn-primary ml-1"
                            @click="select">
                            {{ translate('cp.select') }}
                        </button>
                    </div>
                </div>

            </div>
        </modal>


</div>

</template>

<script>
export default {
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
        select() {
            this.$emit('selected', this.browserSelections);
            this.close();
        },

        /**
         * Close this selector
         */
        close() {
            this.$modal.hide('asset-selector');
            this.$emit('closed');
        },

        /**
         * Selections have been updated within the browser component.
         */
        selectionsUpdated(selections) {
            this.browserSelections = selections;
        }

    },

    mounted() {
        this.$modal.show('asset-selector');
    }

};
</script>
