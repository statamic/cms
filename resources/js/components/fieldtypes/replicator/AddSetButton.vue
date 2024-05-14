<template>

    <div class="replicator-set-picker">
        <set-picker :sets="groups" @added="addSet">
            <template #trigger>
                <div class="replicator-set-picker-button-wrapper">
                    <button v-if="enabled" class="btn-round flex items-center justify-center" :class="{ 'h-5 w-5': ! last }" @click="addSetButtonClicked">
                        <svg-icon name="micro/plus"
                            :class="{
                                'w-3 h-3 text-gray-800 dark:text-dark-175 group-hover:text-black dark:group-hover:dark-text-100': last,
                                'w-2 h-2 text-gray-700 dark:text-dark-200 group-hover:text-black dark:group-hover:dark-text-100 transition duration-150': !last
                            }" />
                    </button>
                </div>
            </template>
        </set-picker>
    </div>

</template>

<script>
import SetPicker from './SetPicker.vue';

export default {

    components: {
        SetPicker,
    },

    props: {
        sets: Array,
        groups: Array,
        index: Number,
        last: Boolean,
        enabled: { type: Boolean, default: true },
    },

    methods: {

        addSet(handle) {
            this.$emit('added', handle, this.index);
        },

        addSetButtonClicked() {
            if (this.sets.length === 1) {
                this.addSet(this.sets[0].handle);
            }
        }

    }

}
</script>
