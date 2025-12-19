<script setup>
import {
	ref,
	computed,
	onMounted,
	nextTick,
	getCurrentInstance,
	useSlots,
	watch,
	onBeforeUnmount,
	provide
} from 'vue';
import { stacks, portals, events, keys, config } from '@/api';
import wait from '@/util/wait.js';
import {hasComponent} from "@/composables/has-component.js";
import { Button, Heading } from "@ui";
import Icon from "@ui/Icon/Icon.vue";

const slots = useSlots();
const emit = defineEmits(['update:open', 'opened']);

const props = defineProps({
	/** Title displayed at the top of the stack */
	title: { type: String, default: '' },
	/** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
	icon: { type: [String, null], default: null },
	/** The controlled open state of the stack. */
	open: { type: Boolean, default: false },
	/** Callback that fires before the stack closes. */
	beforeClose: { type: Function, default: () => true },
	/** Controls the size of the stack. Options: `narrow`, `half`, `full` */
	size: { type: String, default: null },
});

const stack = ref(null);
const mounted = ref(false);
const visible = ref(false);
const isHovering = ref(false);
const escBinding = ref(null);
const windowInnerWidth = ref(window.innerWidth);

const instance = getCurrentInstance();
const hasStackTitleComponent = hasComponent('StackTitle');
const isUsingOpenProp = computed(() => instance?.vnode.props?.hasOwnProperty('open'));
const portal = computed(() => stack.value ? `#portal-target-${stack.value.id}` : null);
const depth = computed(() => stack.value?.data.depth);
const isTopStack = computed(() => stacks.count() === depth.value);

const offset = computed(() => {
	if (isTopStack.value && props.size === 'narrow') {
		return windowInnerWidth.value - 450;
	} else if (isTopStack.value && props.size === 'half') {
		return windowInnerWidth.value / 2;
	}

	// max of 200px, min of 80px
	return Math.max(450 / (stacks.count() + 1), 80);
});

const leftOffset = computed(() => {
	if (props.size === 'full') {
		return 0;
	}

	if (isTopStack.value && (props.size === 'narrow' || props.size === 'half')) {
		return offset.value;
	}

	return offset.value * depth.value;
});

const hasChild = computed(() => stacks.count() > depth.value);
const direction = computed(() => config.get('direction', 'ltr'));

const clickedHitArea = () => {
	if (!visible.value) return;
	if (!runCloseCallback()) return;

	events.$emit(`stacks.${depth.value - 1}.hit-area-mouseout`);
};

const mouseEnterHitArea = () => {
	if (!visible.value) return;

	events.$emit(`stacks.${depth.value - 1}.hit-area-mouseenter`);
};

const mouseOutHitArea = () => {
	if (!visible.value) return;

	events.$emit(`stacks.${depth.value - 1}.hit-area-mouseout`);
};

const windowResized = () => windowInnerWidth.value = window.innerWidth;

function open() {
	if (!stack.value) stack.value = stacks.add(instance.proxy);

	events.$on(`stacks.${depth.value}.hit-area-mouseenter`, () => (isHovering.value = true));
	events.$on(`stacks.${depth.value}.hit-area-mouseout`, () => (isHovering.value = false));

	escBinding.value = keys.bindGlobal('esc', close);

	window.addEventListener('resize', windowResized);

	nextTick(() => {
		mounted.value = true;
		updateOpen(true);

		nextTick(() => {
			visible.value = true;
			emit('opened');
		});
	});
}

function close() {
	visible.value = false;

	events.$off(`stacks.${depth.value}.hit-area-mouseenter`);
	events.$off(`stacks.${depth.value}.hit-area-mouseout`);

	window.removeEventListener('resize', windowResized);

	stack.value?.destroy();
	stack.value = null;

	escBinding.value?.destroy();
	escBinding.value = null;

	wait(300).then(() => {
		mounted.value = false;
		updateOpen(false);
	});
}

function updateOpen(value) {
	if (isUsingOpenProp.value && props.open !== value) {
		emit('update:open', value);
	}
}

function runCloseCallback() {
	const shouldClose = props.beforeClose();

	if (!shouldClose) return false;

	close();

	return true;
}

watch(
	() => props.open,
	(value) => value ? open() : close(),
);

onMounted(() => {
	if (props.open) open();
});

onBeforeUnmount(() => {
	events.$off(`stacks.${depth.value}.hit-area-mouseenter`);
	events.$off(`stacks.${depth.value}.hit-area-mouseout`);

	window.removeEventListener('resize', windowResized);

	stack.value?.destroy();
	escBinding.value?.destroy();
});

defineExpose({
	open,
	close,
	runCloseCallback,
});

provide('closeStack', close);
</script>

<template>
	<div v-if="slots.trigger" @click="open">
		<slot name="trigger" />
	</div>
    <teleport :to="portal" :order="depth" v-if="mounted">
        <div class="vue-portal-target stack">
            <div
                class="stack-container"
                :class="{ 'stack-is-current': isTopStack }"
                :style="direction === 'ltr' ? { left: `${leftOffset}px` } : { right: `${leftOffset}px` }"
            >
                <transition name="stack-overlay-fade">
                    <div
                        v-if="visible"
                        class="stack-overlay fixed inset-0 bg-gray-800/20 dark:bg-gray-800/50 backdrop-blur-[2px]"
                        :style="direction === 'ltr' ? { left: `-${leftOffset}px` } : { right: `-${leftOffset}px` }"
                    />
                </transition>

                <div
                    class="stack-hit-area"
                    :style="direction === 'ltr' ? { left: `-${offset}px` } : { right: `-${offset}px` }"
                    @click="clickedHitArea"
                    @mouseenter="mouseEnterHitArea"
                    @mouseout="mouseOutHitArea"
                />

                <transition name="stack-slide">
                    <div
                        v-if="visible"
                        data-ui-stack-content
                        class="stack-content fixed flex flex-col sm:end-1.5 overflow-auto bg-white dark:bg-gray-850 rounded-xl shadow-[0_8px_5px_-6px_rgba(0,0,0,0.1),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.15)] dark:shadow-[0_5px_20px_rgba(0,0,0,.5)] transition-transform duration-150 ease-out"
                        :class="[
                            size === 'full' ? 'inset-2 w-[calc(100svw-1rem)]' : 'inset-y-2',
                            { '-translate-x-4 rtl:translate-x-4': isHovering }
                        ]"
                    >
	                    <div
		                    v-if="!hasStackTitleComponent && (title || icon)"
		                    data-ui-stack-title
		                    class="flex items-center justify-between rounded-t-xl border-b border-gray-300 px-4 mb-3 py-2 dark:border-gray-950 dark:bg-gray-800"
	                    >
		                    <div class="flex items-center gap-2">
			                    <Icon :name="icon" v-if="icon" class="size-4" />
			                    <Heading size="lg" :text="title" />
		                    </div>
		                    <Button icon="x" variant="ghost" class="-me-2" @click="close" />
	                    </div>

                        <slot />
                    </div>
                </transition>
            </div>
        </div>
    </teleport>
</template>