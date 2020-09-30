<template>
    <label for="checkerOfAllBoxes" class="btn btn-sm px-sm flex items-center justify-center -ml-sm relative -left-px cursor-pointer">
        <input type="checkbox" @change="toggle" :checked="anyItemsChecked" id="checkerOfAllBoxes">
    </label>
</template>

<script>
export default {
    inject: ['sharedState'],
    computed: {
        allItemsChecked() {
            if (this.sharedState.rows.length === 0) return false;

            return this.sharedState.selections.length === this.sharedState.rows.length;
        },
        anyItemsChecked() {
            return this.sharedState.selections.length > 0;
        },
    },
    methods: {
        toggle() {
            this.anyItemsChecked ? this.uncheckAllItems() : this.checkAllItems()
        },

        checkAllItems() {
            this.sharedState.selections = _.values(_.map(this.sharedState.rows, item => item.id))
        },

        uncheckAllItems() {
            this.sharedState.selections = []
        },
    },
}
</script>
