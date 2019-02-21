<template>

    <div class="text-2xs">
        <div
            v-for="item in value"
            :key="item.id"
            class="flex items-center"
        >
            <div class="little-dot mr-1" :class="[item.published ? 'bg-green' : 'bg-grey-40']" />
            <a :href="item.edit_url" @click.prevent="isEditing=item">{{ item.title }}</a>
        </div>

        <inline-edit-form
            v-if="isEditing"
            :item="isEditing"
            @updated="isEditing.title = $event.title"
            @closed="isEditing = false"
        />
    </div>

</template>

<script>
import InlineEditForm from '../../inputs/relationship/InlineEditForm.vue';

export default {
    mixins: [IndexFieldtype],
    components: {
        InlineEditForm
    },
    data() {
        return {
            isEditing: false
        }
    }
}
</script>
