<template>

    <div
        class="item select-none"
        :class="{ 'invalid': item.invalid }"
    >
        <div class="item-move" v-if="sortable">&nbsp;</div>
        <div class="item-inner">
            <div v-if="statusIcon" class="little-dot mr-1" :class="item.status" />

            <div
                v-if="item.invalid"
                v-tooltip.top="__('An item with this ID could not be found')"
                v-text="item.title" />

            <a v-if="!item.invalid && editable" @click="edit" v-text="item.title" />

            <div v-if="!item.invalid && !editable" v-text="item.title" />

            <inline-edit-form
                v-if="isEditing"
                :item="item"
                :component="formComponent"
                :component-props="formComponentProps"
                @updated="itemUpdated"
                @closed="isEditing = false"
            />

        </div>

        <div v-if="item.collection" v-text="item.collection.title" class="text-4xs text-grey-60 uppercase whitespace-no-wrap mr-1" />

        <div class="pr-1 flex items-center" v-if="!readOnly">
            <dropdown-list>
                <dropdown-item :text="__('Edit')" @click="edit" v-if="editable" />
                <dropdown-item :text="__('Unlink')" class="warning" @click="$emit('removed')" />
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
        formComponent: String,
        formComponentProps: Object,
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
        },

        itemUpdated(responseData) {
            this.item.title = responseData.title;
            this.item.published = responseData.published;
            this.item.private = responseData.private;
            this.item.status = responseData.status;
        },

    }

}
</script>
