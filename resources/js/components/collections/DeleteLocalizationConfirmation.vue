<template>

    <modal name="delete-entry-confirmation" :pivotY="0.1" :overflow="false">
        <div class="confirmation-modal flex flex-col h-full">
            <div class="text-lg font-medium p-2 pb-0">
                {{ __('Handle localized entries') }}
            </div>
            <div class="flex-1 px-2 py-3 text-grey">
                <p class="mb-2" v-html='`How would you like to handle localized versions of the entries being deleted? <a href="" target="_blank">More info.</a>`' />
                <div class="radio-fieldtype-wrapper">
                    <div class="option">
                        <label><input type="radio" value="delete" v-model="behavior"> Delete localized versions </label>
                    </div>
                    <div class="option">
                        <label><input type="radio" value="copy" v-model="behavior"> Copy data to localizations </label>
                    </div>
                </div>
            </div>
            <div class="p-2 bg-grey-20 border-t flex items-center justify-end text-sm">
                <button class="text-grey hover:text-grey-90"
                    @click="$emit('cancel')"
                    v-text="__('Cancel')" />
                <button class="btn ml-2 btn-danger"
                    @click="$emit('confirm', behavior)"
                    v-text="__('Delete')" />
            </div>
        </div>
    </modal>

</template>

<script>
export default {

    data() {
        return {
            behavior: null
        }
    }

}
</script>
