<template>
    <div class="data-grid">
        <div class="asset-browser-grid flex flex-wrap -mx-1 px-2 pt-2">
            <div class="w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6 mb-2 px-1 group" v-for="asset in assets">
                <!-- 4:3 ratio container -->
                <div class="w-full relative text-center" style="padding-top:75%" @click="toggleSelection(asset.id)">
                    <!-- hover controls -->
                    <div class="absolute pin z-20 opacity-0 ease group-hover:opacity-100">

                    </div>

                    <!-- thumbnail -->
                    <div class="absolute pin flex items-center justify-center" :class="{ 'selected': isSelected(asset.id) }">
                        <img
                            class="max-h-full max-w-full rounded lazyload"
                            :data-src="asset.thumbnail" />
                    </div>
                </div>
                <!-- filename -->
                <div class="text-3xs text-center text-grey-70 pt-sm w-full text-truncate" v-text="asset.basename" :title="asset.basename" />
            </div>
        </div>
    </div>
</template>

<script>

export default {

    components: {

    },

    props: {
        loading: {
            type: Boolean,
            default: false
        },
        items: {
            type: Array
        },
        allowBulkActions: {
            default: false,
            type: Boolean
        },
    },

    inject: ['sharedState'],

    computed: {

        assets() {
            return this.sharedState.rows;
        },

        reachedSelectionLimit() {
            return this.sharedState.selections.length === this.sharedState.maxSelections;
        },
    },

    methods: {

        actualIndex(row) {
            return _.findIndex(this.sharedState.originalRows, row);
        },

        isSelected(id) {
            return this.sharedState.selections.includes(id);
        },

        toggleSelection(id) {
            const i = this.sharedState.selections.indexOf(id);

            if (i != -1) {
                this.sharedState.selections.splice(i, 1);
            } else if (! this.reachedSelectionLimit) {
                this.sharedState.selections.push(id);
            }
        }

    }
}
</script>
