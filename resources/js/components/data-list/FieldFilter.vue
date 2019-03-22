<template>

    <div class="form-group">
        <label>{{ field.display }}</label>
        <div class="flex items-center text-sm">
            <select-input
                class="w-1/5 mr-1"
                name="operator"
                v-model="value.operator"
                :options="[
                    { text: 'Equal to', value: '=' },
                    { text: 'Not equal to', value: '<>' },
                    { text: 'Like', value: 'like' },
                ]" />

            <div class="flex-1">
                <text-input name="value" v-model="value.value" />
            </div>

            <button
                type="button"
                class="ml-2 p-1 text-xl text-grey-60"
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
