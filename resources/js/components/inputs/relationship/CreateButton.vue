<script>
import InlineCreateForm from './InlineCreateForm.vue';

export default {
    components: {
        InlineCreateForm,
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
        };
    },

    computed: {
        isCreating() {
            return this.creatable !== null;
        },
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
            if (this.creatables.length === 1) this.select(this.creatables[0]);
        },

        select(creatable) {
            this.creatable = creatable;
        },
    },
};
</script>

<template>
    <div>
        <dropdown-list :disabled="creatables.length === 1">
            <template #trigger>
                <button
                    class="text-button flex items-center text-blue outline-hidden hover:text-gray-800 dark:text-dark-blue-100 dark:hover:text-dark-100 ltr:mr-6 rtl:ml-6"
                    @click="create"
                >
                    <svg-icon
                        name="light/content-writing"
                        class="flex h-4 w-4 items-center ltr:mr-1 rtl:ml-1"
                    ></svg-icon>
                    <span class="hidden @sm:block" v-text="__('Create & Link Item')" />
                    <span class="@sm:hidden" v-text="__('Create')" />
                </button>
            </template>

            <dropdown-item
                v-for="creatable in creatables"
                :key="creatable.url"
                :text="creatable.title"
                @click="select(creatable)"
            />
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