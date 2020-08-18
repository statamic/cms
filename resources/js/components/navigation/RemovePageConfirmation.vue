<template>

    <modal name="remove-page-confirmation" :pivotY="0.1" :overflow="false">
        <div class="confirmation-modal flex flex-col h-full">
            <div class="text-lg font-medium p-2 pb-0">
                {{ __('Remove Page') }}
            </div>
            <div class="flex-1 px-2 py-3 text-grey">
                <p class="mb-2" v-text="__('Are you sure you want to remove this page?')" />
                <p class="mb-2" v-text="__('Only the references will be removed. Entries will not be deleted.')" />
                <label class="flex items-center" v-if="children">
                    <input type="checkbox" class="mr-1" v-model="shouldDeleteChildren" />
                    {{ __n('Remove child page|Remove :count child pages', children) }}
                </label>
            </div>
            <div class="p-2 bg-grey-20 border-t flex items-center justify-end text-sm">
                <button class="text-grey hover:text-grey-90"
                    @click="$emit('cancel')"
                    v-text="__('Cancel')" />
                <button class="btn ml-2 btn-danger"
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
