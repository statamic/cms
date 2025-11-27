<script setup>
import { ref, computed, useSlots, watch, onMounted, Comment, Fragment } from 'vue';
import { injectListingContext } from '../Listing/Listing.vue';
import TableHead from './TableHead.vue';
import TableBody from './TableBody.vue';

const props = defineProps({
    unstyled: {
        type: Boolean,
        default: false,
    },
    contained: {
        type: Boolean,
        default: false,
    },
});

const { visibleColumns, selections, items, hasActions, showBulkActions, loading, reorderable } = injectListingContext();
const shifting = ref(false);
const hasSelections = computed(() => selections.value.length > 0);

const relativeColumnsSize = computed(() => {
    if (visibleColumns.value.length <= 4) return 'sm';
    if (visibleColumns.value.length <= 8) return 'md';
    if (visibleColumns.value.length >= 12) return 'lx';
    return 'xl';
});

const slots = useSlots();

const forwardedTableCellSlots = computed(() => {
    return Object.keys(slots)
        .filter((slotName) => slotName.startsWith('cell-'))
        .reduce((acc, slotName) => {
            acc[slotName] = slots[slotName];
            return acc;
        }, {});
});

const hasTbodyStartContent = ref(false);

const checkSlotContent = () => {
	if (!slots['tbody-start']) {
		hasTbodyStartContent.value = false;
		return;
	}

	const slotContent = slots['tbody-start']();

	const hasRealContent = (vnodes) => {
		if (!vnodes || vnodes.length === 0) return false;

		return vnodes.some(vnode => {
			// Skip comments
			if (vnode.type === Comment) return false;

			// Skip empty text nodes
			if (typeof vnode.children === 'string' && !vnode.children.trim()) return false;

			// Handle fragments (like from v-for)
			if (vnode.type === Fragment) {
				return hasRealContent(vnode.children);
			}

			// If it has array children, recursively check them
			if (Array.isArray(vnode.children)) {
				return hasRealContent(vnode.children);
			}

			// Otherwise it's real content
			return true;
		})
	}

	hasTbodyStartContent.value = hasRealContent(slotContent)
}

watch(items, () => checkSlotContent(), { immediate: true, deep: true });

onMounted(checkSlotContent);
</script>

<template>
    <table
        v-if="items.length > 0 || hasTbodyStartContent"
        :data-size="relativeColumnsSize"
        :class="{
            'select-none': shifting,
            'data-table': !unstyled,
            'data-table--contained': contained,
            'opacity-50': loading,
        }"
        data-table
        ref="table"
        :data-has-selections="hasSelections ? true : null"
        @keydown.shift="shifting = true"
        @keyup="shifting = false"
    >
        <TableHead />
        <TableBody>
            <template v-if="$slots['tbody-start']" #tbody-start><slot name="tbody-start" /></template>
            <template v-if="$slots['prepended-row-actions']" #prepended-row-actions="slotProps">
                <slot name="prepended-row-actions" v-bind="slotProps" />
            </template>
            <template v-for="(slot, slotName) in forwardedTableCellSlots" :key="slotName" #[slotName]="slotProps">
                <component :is="slot" v-bind="slotProps" />
            </template>
        </TableBody>
    </table>
    <div v-if="items.length === 0 && !hasTbodyStartContent">
        <div class="text-center text-gray-500 text-sm py-4">
            {{ __('No items found') }}
        </div>
    </div>
</template>
