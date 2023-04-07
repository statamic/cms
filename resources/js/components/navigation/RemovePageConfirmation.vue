<template>

    <modal name="remove-page-confirmation">
        <div class="confirmation-modal flex flex-col h-full">
            <div class="text-lg font-medium p-4 pb-0">
                {{ __('Remove Page') }}
            </div>
            <div class="flex-1 px-4 py-6 text-gray">
                <p class="mb-4" v-text="__('Are you sure you want to remove this page?')" />
                <p class="mb-4" v-text="__('Only the references will be removed. Entries will not be deleted.')" />
                <label class="flex items-center" v-if="children">
                    <input type="checkbox" class="mr-2" v-model="shouldDeleteChildren" />
                    {{ __n('Remove child page|Remove :count child pages', children) }}
                </label>
            </div>
            <div class="p-4 bg-gray-200 border-t flex items-center justify-end text-sm">
                <button class="text-gray hover:text-gray-900"
                    @click="$emit('cancel')"
                    v-text="__('Cancel')" />
                <button class="btn ml-4 btn-danger"
                    @click="$emit('confirm', shouldDeleteChildren)"
                    v-text="__('Remove')" />
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
