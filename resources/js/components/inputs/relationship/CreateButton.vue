<template>
    <div>
        <ui-button
            v-if="creatables.length === 1"
            :icon="icon"
            :text="text"
            size="sm"
            @click="create"
        />
        <ui-dropdown v-else-if="creatables.length > 1">
            <template #trigger>
                <ui-button
                    :icon="icon"
                    :text="text"
                    size="sm"
                />
            </template>
            <ui-dropdown-menu>
                <ui-dropdown-label :text="menuLabel || __('Choose Blueprint')" />
                <ui-dropdown-item
                    v-for="creatable in creatables"
                    :key="creatable.url"
                    @click="select(creatable)"
                >
                    <div class="flex items-center justify-between">
                        <span>{{ creatable.label ?? creatable.blueprint }}</span>
                        <ui-badge v-if="creatable.hint ?? creatable.parent_title" size="sm">{{ creatable.hint ?? creatable.parent_title }}</ui-badge>
                    </div>
                </ui-dropdown-item>
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
        menuLabel: String,
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
