<template>

    <modal name="delete-entry-confirmation">
        <div class="confirmation-modal flex flex-col h-full">
            <div class="text-lg font-medium p-4 pb-0">
                {{ __('Delete Entry') }}
            </div>
            <div class="flex-1 px-4 py-6 text-gray">
                <p class="mb-4" v-text="__('Are you sure you want to delete this entry?')" />
                <label class="flex items-center" v-if="children">
                    <input type="checkbox" class="mr-2" v-model="shouldDeleteChildren" />
                    {{ __n('Delete child entry|Delete :count child entries', children) }}
                </label>
            </div>
            <div class="p-4 bg-gray-200 border-t flex items-center justify-end text-sm">
                <button class="text-gray hover:text-gray-900"
                    @click="$emit('cancel')"
                    v-text="__('Cancel')" />
                <button class="btn ml-4 btn-danger"
                    @click="$emit('confirm', shouldDeleteChildren)"
                    v-text="__('Delete')" />
            </div>
        </div>
    </modal>

</template>

<script>
export default {

    props: {
        children: Number
    },

    data() {
        return {
            shouldDeleteChildren: false
        }
    }

}
</script>
