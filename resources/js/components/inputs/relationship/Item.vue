<template>

    <div
        class="item mb-1 select-none"
        :class="{ 'published': item.published, 'unpublished': !item.published, 'invalid': item.invalid }"
    >
        <div class="item-move" v-if="sortable">&nbsp;</div>
        <div class="item-inner">
            <div v-if="statusIcon" class="little-dot mr-1" />

            <div
                v-if="item.invalid"
                v-tooltip.top="__('An item with this ID could not be found')"
                v-text="item.title" />

            <a v-if="!item.invalid && editable" @click="edit" v-text="item.title" />

            <div v-if="!item.invalid && !editable" v-text="item.title" />

            <inline-edit-form
                v-if="isEditing"
                :item="item"
                @updated="item.title = $event.title"
                @closed="isEditing = false"
            />

        </div>

        <div v-if="item.collection" v-text="item.collection" class="text-4xs text-grey-60 uppercase mr-1" />

        <div class="pr-1 flex items-center" v-if="!readOnly">
            <dropdown-list>
                <ul class="dropdown-menu">
                    <li v-if="editable"><a @click.prevent="edit" v-text="__('Edit')"></a></li>
                    <li class="warning"><a @click.prevent="$emit('removed')" v-text="__('Unlink')"></a></li>
                </ul>
            </dropdown-list>
        </div>
    </div>

</template>

<script>
import InlineEditForm from './InlineEditForm.vue';

export default {

    components: {
        InlineEditForm
    },

    props: {
        item: Object,
        config: Object,
        statusIcon: Boolean,
        editable: Boolean,
        sortable: Boolean,
        readOnly: Boolean,
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
