<template>

    <modal name="delete-entry-confirmation" :pivotY="0.1" :overflow="false">
        <div class="confirmation-modal flex flex-col h-full">
            <div class="text-lg font-medium p-4___REPLACED pb-0">
                {{ __('Delete Entry') }}
            </div>
            <div class="flex-1 px-2 py-3 text-grey">
                <p class="mb-4___REPLACED" v-text="__('Are you sure you want to delete this entry?')" />
                <label class="flex items-center" v-if="children">
                    <input type="checkbox" class="mr-2___REPLACED" v-model="shouldDeleteChildren" />
                    {{ __n('Delete child entry|Delete :count child entries', children) }}
                </label>
            </div>
            <div class="p-4___REPLACED bg-grey-20 border-t flex items-center justify-end text-sm">
                <button class="text-grey hover:text-grey-90"
                    @click="$emit('cancel')"
                    v-text="__('Cancel')" />
                <button class="btn ml-4___REPLACED btn-danger"
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
