<template>

    <div>
        <dropdown-list :disabled="creatables.length === 1">
            <template #trigger>
                <button
                    class="text-button text-blue dark:text-dark-blue-100 hover:text-gray-800 dark:hover:text-dark-100 rtl:ml-6 ltr:mr-6 flex items-center outline-none"
                    @click="create"
                >
                    <svg-icon name="light/content-writing" class="rtl:ml-1 ltr:mr-1 h-4 w-4 flex items-center"></svg-icon>
                    <span class="hidden @sm:block" v-text="__('Create & Link Item')" />
                    <span class="@sm:hidden" v-text="__('Create')" />
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
            :stack-size="stackSize"
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
        stackSize: String,
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
