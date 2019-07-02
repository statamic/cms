<template>

    <div class="form-group">
        <label>{{ field.display }}</label>
        <div class="flex items-center text-sm">
            <select-input
                class="w-1/5 mr-1"
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

            <button
                type="button"
                class="btn-close"
                @click="$emit('removed')"
                v-html="'&times'" />
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
