<template>
    <th v-show="field.type !== 'hidden'" scope="col">
        <div class="flex items-center justify-between">
            <div>
                <template v-if="!field.hide_display">{{ __(field.display || field.handle) }}</template>
            </div>
            <ui-icon name="info-square" class="size-3 text-xs text-gray-600 hover:text-gray-700" v-if="field.instructions" v-tooltip="{ content: $markdown(__(field.instructions)), html: true }" />
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
