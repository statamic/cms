<template>

    <div>
        <label class="mb-1">{{ field.display }}</label>
        <div class="flex items-center text-sm">
            <select-input
                class="w-1/5 mr-2"
                name="operator"
                v-model="value.operator"
                :options="[
                    { label: 'Equal to', value: '=' },
                    { label: 'Not equal to', value: '<>' },
                    { label: 'Like', value: 'like' },
                ]" />

            <div class="flex-1">
                <text-input name="value" v-model="value.value" />
            </div>

            <button @click="$emit('removed')" class="btn-close group">
                <svg-icon name="trash" class="w-auto group-hover:text-red" />
            </button>

        </div>
    </div>

</template>

<script>
export default {

    props: {
        field: Object,
        value: {
            type: Object,
            default: () => {
                return {
                    value: null,
                    operator: '=',
                }
            }
        }
    },

    watch: {

        value: {
            deep: true,
            handler(value) {
                this.$emit('updated', value);
            }
        }

    }

}
</script>
