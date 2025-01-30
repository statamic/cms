<template>

    <modal name="remove-page-confirmation">
        <div class="confirmation-modal flex flex-col h-full">
            <div class="text-lg font-medium p-4 pb-0">
                {{ __('Remove Page') }}
            </div>
            <div class="flex-1 px-4 py-6 text-gray dark:text-dark-150">
                <p class="mb-4" v-text="__('Are you sure you want to remove this page?')" />
                <p class="mb-4" v-text="__('Only the references will be removed. Entries will not be deleted.')" />
                <label class="flex items-center" v-if="children">
                    <input type="checkbox" class="rtl:ml-2 ltr:mr-2" v-model="shouldDeleteChildren" />
                    {{ __n('Remove child page|Remove :count child pages', children) }}
                </label>
            </div>
            <div class="p-4 bg-gray-200 dark:bg-dark-550 border-t dark:border-dark-900 flex items-center justify-end text-sm">
                <button class="text-gray dark:text-dark-150 hover:text-gray-900 dark:hover:text-dark-100"
                    @click="$emit('cancel')"
                    v-text="__('Cancel')" />
                <button class="rtl:mr-4 ltr:ml-4 btn-danger"
                    @click="$emit('confirm', shouldDeleteChildren)"
                    v-text="__('Remove')" />
            </div>
        </div>
    </modal>

</template>

<script>
export default {

    emits: ['confirm', 'cancel'],

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
