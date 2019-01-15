<template>

    <div
        class="item mb-1"
        :class="{ 'published': item.published, 'unpublished': !item.published, 'invalid': item.invalid }"
    >
        <div class="item-move">&nbsp;</div>
        <div class="item-inner">
            <div v-if="statusIcon" class="little-dot mr-1" />

            <div
                v-if="item.invalid"
                v-popover:tooltip.top="__('An item with this ID could not be found')"
                v-text="item.title" />
            <a v-else  @click="edit" v-text="item.title" />

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

        <!-- <div class="text-5xs px-1 font-bold text-grey-light antialiased uppercase" v-text="item.collection"></div> -->

        <dropdown-list class="pr-1">
            <ul class="dropdown-menu">
                <li><a @click.prevent="edit" v-text="__('Edit')"></a></li>
                <li class="warning"><a @click.prevent="$emit('removed')" v-text="__('Unlink')"></a></li>
            </ul>
        </dropdown-list>
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
