<template>

    <div class="replicator-set-picker">
        <set-picker :sets="groups" :placement="last ? 'bottom-start' : 'auto'" @added="addSet">
            <template #trigger>
                <div class="text-center">
                    <button :class="{ 'btn-round flex items-center justify-center': last }" @click="addSetButtonClicked">
                        <svg-icon name="micro-plus"
                            :class="{
                                'w-3 h-3 text-gray-800 group-hover:text-black': last,
                                'w-2.5 h-2.5 text-gray-600 group-hover:text-gray-900 transition duration-150': !last
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
