<template>

    <div
        class="bg-grey-lightest shadow mb-2 rounded border"
        :class="{ 'opacity-50': isExcessive }"
    >
        <div
            class="cursor-move bg-grey-lighter border-b px-2 py-1 text-sm flex items-center justify-between"
            :class="sortableHandleClass"
        >
            {{ index }}
            <span class="icon icon-cross cursor-pointer" @click="$emit('removed', index)" />
        </div>
        <publish-field
            v-for="field in fields"
            :key="field.handle"
            :config="field"
            :value="values[field.handle]"
            class="p-2"
            @updated="updated"
        />
    </div>

</template>

<style scoped>
    .draggable-mirror {
        position: relative;
        z-index: 1000;
    }
    .draggable-source--is-dragging {
        opacity: 0.5;
    }
</style>

<script>
import Row from './Row.vue';
import PublishField from '../../publish/Field.vue';

export default {
    mixins: [Row],
    components: { PublishField }
}
</script>
