<template>

    <div
        class="text-sm mb-1"
        @dblclick="isEditing = true"
    >
        <div class="border shadow-inner bg-grey-lightest rounded-md leading-loose px-1 inline-flex items-center cursor-pointer select-none">
            <div class="little-dot bg-green mr-1" />

            {{ item.title }}

            <button
                class="text-xs text-grey ml-1 font-bold outline-none hover:text-red"
                @click.prevent="$emit('removed')">
                &times;
            </button>

            <popper
                v-if="isEditing"
                ref="popper"
                trigger="click"
                :force-show="isEditing"
                :append-to-body="true"
                boundaries-selector="body"
                :options="{ placement: 'right' }"
            >
                <div class="popover w-96 h-96 p-0">
                    <inline-publish-form
                        :item="item"
                        @updated="item.title = $event.title"
                        @closed="isEditing = false"
                    />
                </div>

                <!-- Popper needs a clickable element, but we don't want one.
                We'll show it programatically.  -->
                <div slot="reference" class="-mr-1" />
            </popper>

        </div>
    </div>

</template>

<script>
import Popper from 'vue-popperjs';
import InlinePublishForm from './InlinePublishForm.vue';

export default {

    components: {
        Popper,
        InlinePublishForm
    },

    props: {
        item: Object
    },

    data() {
        return {
            isEditing: false,
        }
    }

}
</script>
