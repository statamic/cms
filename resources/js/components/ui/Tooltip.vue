<script setup>
import { TooltipArrow, TooltipContent, TooltipPortal, TooltipProvider, TooltipRoot, TooltipTrigger } from 'reka-ui';
import { computed } from 'vue';

const props = defineProps({
    text: { type: String, default: null },
    markdown: { type: String, default: null },
    delay: { type: Number, default: 0 },
});

const tooltipText = computed(() => (props.markdown ? markdown(props.markdown) : props.text));
</script>

<template>
    <TooltipProvider :ariaLabel="tooltipText" :delay-duration="delay">
        <TooltipRoot>
            <TooltipTrigger as-child>
                <slot />
            </TooltipTrigger>
            <TooltipPortal>
                <TooltipContent
                    :class="[
                        'data-[state=delayed-open]:data-[side=top]:animate-slideDownAndFade',
                        'data-[state=delayed-open]:data-[side=right]:animate-slideLeftAndFade',
                        'data-[state=delayed-open]:data-[side=left]:animate-slideRightAndFade',
                        'data-[state=delayed-open]:data-[side=bottom]:animate-slideUpAndFade',
                        'rounded-xl bg-white px-3 py-2',
                        'text-xs leading-none text-gray-600',
                        'shadow-ui-sm border border-gray-300',
                        'will-change-[transform,opacity]',
                    ]"
                    :side-offset="5"
                    role="tooltip"
                    :aria-label="tooltipText"
                >
                    <span v-html="tooltipText" />
                    <TooltipArrow class="fill-white stroke-gray-300" :width="12" :height="6" />
                </TooltipContent>
            </TooltipPortal>
        </TooltipRoot>
    </TooltipProvider>
</template>
