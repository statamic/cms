<template>
    <Button
        class="px-2!"
        :class="{ active, group: variant === 'floating' }"
        :variant="variant === 'floating' ? 'subtle' : 'ghost'"
        size="sm"
        :aria-label="tooltipText"
        v-tooltip="tooltipText"
        @click="button.command(editor, button.args)"
    >
        <ui-icon :name="button.svg" v-if="button.svg" class="size-3.5! " :class="{ 'group-hover:text-white text-yellow-300!': active && variant === 'floating' }" />
        <span v-if="variant === 'floating' && (button.text || button.shortcutKey)" class="ml-1 text-xs">{{ button.text }}{{ button.shortcutKey ? ` ${button.shortcutKey}` : '' }}</span>
        <div class="flex items-center" v-html="button.html" v-if="button.html" />
    </Button>
</template>

<script>
import { Button } from '@/components/ui';

export default {
    components: {
        Button,
    },
    props: {
        button: Object,
        active: Boolean,
        variant: String,
        config: Object,
        bard: {},
        editor: {},
    },
    computed: {
        tooltipText() {
            const label = this.button?.text ?? '';
            if (this.button?.shortcutKey) {
                return label ? `${label} (${this.button.shortcutKey})` : this.button.shortcutKey;
            }
            return label;
        },
    },
};
</script>
