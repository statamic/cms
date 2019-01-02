<template>

    <div
        class="item text-sm mb-1"
        @dblclick="edit"
    >
        <div
            class="item-inner border shadow-inner bg-grey-lightest rounded-md leading-loose px-1 inline-flex items-center cursor-pointer select-none"
            :class="{ 'border-red bg-red-lighter text-red': item.invalid }"
        >
            <div
                v-if="statusIcon"
                class="little-dot mr-1"
                :class="{ 'bg-green': item.published, 'bg-grey-light': !item.published, 'bg-red': item.invalid }"
            />

            <span
                v-if="item.invalid"
                v-popover:tooltip.top="__('An item with this ID could not be found')"
                v-text="item.title" />
            <span v-else v-text="item.title" />

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
                    <inline-edit-form
                        class="popover-inner"
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
import InlineEditForm from './InlineEditForm.vue';

export default {

    components: {
        Popper,
        InlineEditForm
    },

    props: {
        item: Object,
        statusIcon: Boolean,
        editable: Boolean
    },

    data() {
        return {
            isEditing: false,
        }
    },

    methods: {

        edit() {
            if (! this.editable) return;
            if (this.item.invalid) return;
            this.isEditing = true;
        }

    }

}
</script>
