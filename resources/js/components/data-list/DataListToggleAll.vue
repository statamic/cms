<template>
    <div class="p-1">
        <input type="checkbox" @change="toggle" :checked="allItemsChecked">
    </div>
</template>

<script>
export default {
    inject: ['sharedState'],
    computed: {
        allItemsChecked() {
            return this.sharedState.checkedIds.length === Object.values(this.sharedState.rows).length;
        },
    },
    methods: {
        toggle() {
            this.allItemsChecked ? this.uncheckAllItems() : this.checkAllItems()
        },

        checkAllItems() {
            this.sharedState.checkedIds = _.values(_.mapObject(this.sharedState.rows, item => item.id))
        },

        uncheckAllItems() {
            this.sharedState.checkedIds = []
        },
    },
}
</script>
