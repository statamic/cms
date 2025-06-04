<template>
    <div>
        <ui-dropdown :disabled="creatables.length === 1">
            <template #trigger>
                <ui-button
                    :icon="icon"
                    variant="filled"
                    :text="text"
                    @click="create"
                />
            </template>
            <ui-dropdown-menu>
                <ui-dropdown-item
                    v-for="creatable in creatables"
                    :key="creatable.url"
                    :text="creatable.title"
                    @click="select(creatable)"
                />
            </ui-dropdown-menu>
        </ui-dropdown>

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
        InlineCreateForm,
    },

    props: {
        site: String,
        creatables: Array,
        component: String,
        componentProps: Object,
        stackSize: String,
        icon: String,
        text: String,
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
