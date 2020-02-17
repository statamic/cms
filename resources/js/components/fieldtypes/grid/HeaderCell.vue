<template>

    <th>
        <div v-if="field.type != 'hidden'" class="flex items-center justify-between">
            <div>{{ field.display || field.handle }}</div>
            <div
                v-if="field.instructions"
                class="icon icon-help-with-circle opacity-50 fs-12"
                :title="$options.filters.markdown(field.instructions)"
            />
        </div>
    </th>

</template>

<script>
export default {

    props: {
        field: {
            type: Object,
            required: true
        }
    }

}
</script>
