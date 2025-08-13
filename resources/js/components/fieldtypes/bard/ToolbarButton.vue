<template>
    <Button
        class="px-2!"
        :class="{ active }"
        variant="ghost"
        size="sm"
        :aria-label="button.text"
        v-tooltip="button.text"
        @click="button.command(editor, button.args)"
    >
        <ui-icon :name="button.svg" v-if="button.svg" class="size-3.5!" />
        <div class="flex items-center" v-html="button.html" v-if="button.html" />
    </Button>
</template>

<script>
import { Button } from '@statamic/ui';

export default {
    components: {
        Button,
    },
    props: {
        button: Object,
        active: Boolean,
        config: Object,
        bard: {},
        editor: {},
    },
};
</script>
