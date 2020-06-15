<template>

    <div>
        <dropdown-list :disabled="creatables.length === 1">
            <template #trigger>
                <button
                    class="text-button text-blue hover:text-grey-80 mr-3 flex items-center outline-none"
                    @click="create"
                >
                    <svg-icon name="content-writing" class="mr-sm h-4 w-4 flex items-center"></svg-icon>
                    {{ __('Create & Link Item') }}
                </button>
            </template>

            <dropdown-item
                v-for="creatable in creatables"
                :key="creatable.url"
                :text="creatable.title"
                @click="select(creatable)" />
        </dropdown-list>

        <inline-create-form
            v-if="isCreating"
            :site="site"
            :item-url="creatable.url"
            :component="component"
            :component-props="componentProps"
            @created="itemCreated"
            @closed="stopCreating"
        />
    </div>

</template>

<script>
import InlineCreateForm from './InlineCreateForm.vue';

export default {

    components: {
        InlineCreateForm
    },

    props: {
        site: String,
        creatables: Array,
        component: String,
        componentProps: Object,
    },

    data() {
        return {
            creatable: null,
        }
    },

    computed: {

        isCreating() {
            return this.creatable !== null;
        }

    },

    methods: {

        itemCreated(item) {
            this.stopCreating();
            this.$emit('created', item);
        },

        stopCreating() {
            this.creatable = null;
        },

        create() {
            if (this.creatables.length === 1)
                this.select(this.creatables[0]);
        },

        select(creatable) {
            this.creatable = creatable;
        }

    }

}
</script>
