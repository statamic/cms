<template>
    <th v-show="field.type !== 'hidden'" scope="col">
        <div class="flex items-center gap-2">
            <div>
                <template v-if="!field.hide_display">{{ __(field.display || field.handle) }}</template>
            </div>
            <ui-icon name="info-square" class="size-3.5! text-xs text-gray-500 hover:text-gray-600" v-if="field.instructions" v-tooltip="{ content: $markdown(__(field.instructions)), html: true }" />
        </div>
    </th>
</template>

<script>
export default {
    props: {
        field: {
            type: Object,
            required: true,
        },
    },
};
</script>
