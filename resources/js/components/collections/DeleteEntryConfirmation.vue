<template>
    <modal name="delete-entry-confirmation">
        <div class="confirmation-modal flex h-full flex-col">
            <div class="p-4 pb-0 text-lg font-medium">
                {{ __('Delete Entry') }}
            </div>
            <div class="flex-1 px-4 py-6 text-gray dark:text-dark-150">
                <p class="mb-4" v-text="__('Are you sure you want to delete this entry?')" />
                <label class="flex items-center" v-if="children">
                    <input type="checkbox" class="ltr:mr-2 rtl:ml-2" v-model="shouldDeleteChildren" />
                    {{ __n('Delete child entry|Delete :count child entries', children) }}
                </label>
            </div>
            <div
                class="flex items-center justify-end border-t bg-gray-200 p-4 text-sm dark:border-dark-900 dark:bg-dark-550"
            >
                <button
                    class="text-gray hover:text-gray-900 dark:text-dark-150 dark:hover:text-dark-100"
                    @click="$emit('cancel')"
                    v-text="__('Cancel')"
                />
                <button
                    class="btn-danger ltr:ml-4 rtl:mr-4"
                    @click="$emit('confirm', shouldDeleteChildren)"
                    v-text="__('Delete')"
                />
            </div>
        </div>
    </modal>
</template>

<script>
export default {
    props: {
        children: Number,
    },

    data() {
        return {
            shouldDeleteChildren: false,
        };
    },
};
</script>
