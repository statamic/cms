<template>
    <div>
        <Header :title="title" icon="users">
            <ItemActions
                ref="actions"
                v-if="canEditBlueprint || hasItemActions"
                :item="values.id"
                :url="itemActionUrl"
                :actions="itemActions"
                :is-dirty="isDirty"
                @started="actionStarted"
                @completed="actionCompleted"
                v-slot="{ actions: itemActions }"
            >
                <Dropdown>
                    <template #trigger>
                        <Button icon="dots" variant="ghost" :aria-label="__('Open dropdown menu')" />
                    </template>
                    <DropdownMenu>
                        <DropdownItem :text="__('Edit Blueprint')" icon="blueprint-edit" v-if="canEditBlueprint" :href="actions.editBlueprint" />
                        <DropdownItem :text="__('Passkeys')" icon="key" :href="cp_url('passkeys')" />
                        <DropdownSeparator v-if="canEditBlueprint && itemActions.length" />
                        <DropdownItem
                            v-for="action in itemActions"
                            :key="action.handle"
                            :text="__(action.title)"
                            :icon="action.icon"
                            :variant="action.dangerous ? 'destructive' : 'default'"
                            @click="action.run"
                        />
                    </DropdownMenu>
                </Dropdown>
            </ItemActions>

            <TwoFactor v-if="twoFactor" v-bind="twoFactor" />

            <change-password
                v-if="canEditPassword"
                :save-url="actions.password"
                :requires-current-password="requiresCurrentPassword"
            />

            <ui-command-palette-item
                :category="$commandPalette.category.Actions"
                :text="__('Save')"
                icon="save"
                :action="save"
                keys="mod+s"
                prioritize
                v-slot="{ text, action }"
            >
                <Button variant="primary" @click.prevent="action" :text="text" />
            </ui-command-palette-item>

            <slot name="action-buttons-right" />
        </Header>

        <PublishContainer
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :reference="initialReference"
            :blueprint="fieldset"
            v-model="values"
            :meta="meta"
            :errors="errors"
        >
            <PublishTabs />
        </PublishContainer>
    </div>
</template>

<script>
import ChangePassword from './ChangePassword.vue';
import HasActions from '../publish/HasActions';
import TwoFactor from '@/components/two-factor/TwoFactor.vue';
import clone from '@/util/clone.js';
import resetValuesFromResponse from '@/util/resetValuesFromResponse.js';
import {
    Button,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    DropdownSeparator,
    PublishContainer,
    PublishTabs,
    Header,
} from '@/components/ui';
import ItemActions from '@/components/actions/ItemActions.vue';
import { computed, ref } from 'vue';
import { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks, PipelineStopped } from '@ui/Publish/SavePipeline.js';

let saving = ref(false);
let errors = ref({});
let container = null;

export default {
    mixins: [HasActions],

    components: {
        ItemActions,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownSeparator,
        Button,
        ChangePassword,
        TwoFactor,
        PublishContainer,
        PublishTabs,
        Header,
    },

    props: {
        publishContainer: String,
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        initialReference: String,
        initialTitle: String,
        actions: Object,
        method: String,
        canEditPassword: Boolean,
        canEditBlueprint: Boolean,
        requiresCurrentPassword: Boolean,
        twoFactor: Object,
    },

    data() {
        return {
            fieldset: clone(this.initialFieldset),
            values: clone(this.initialValues),
            meta: clone(this.initialMeta),
            error: null,
            errors: {},
            title: this.initialTitle,
        };
    },

    computed: {
        isDirty() {
            return this.$dirty.has(this.publishContainer);
        },
    },

    methods: {
        save() {
            new Pipeline()
                .provide({ container, errors, saving })
                .through([
                    new BeforeSaveHooks('user', {
                        values: this.values,
                    }),
                    new Request(this.actions.save, this.method),
                    new AfterSaveHooks('user', {
                        reference: this.initialReference,
                    }),
                ])
                .then((response) => {
                    Statamic.$toast.success(__('Saved'));

                    this.title = response.data.title;

                    this.$nextTick(() => this.$emit('saved', response));
                });
        },

        afterActionSuccessfullyCompleted(response) {
            if (response.data) {
                this.title = response.data.title;
                this.values = resetValuesFromResponse(response.data.values, this.$refs.container);
            }
        },

        addToCommandPalette() {
            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Edit Blueprint'),
                icon: 'blueprint-edit',
                url: this.actions.editBlueprint,
            });

            this.$refs.actions?.preparedActions.forEach(action => Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: action.title,
                icon: action.icon,
                action: action.run,
            }));
        },
    },

    created() {
        container = computed(() => this.$refs.container);
    },

    mounted() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });

        this.addToCommandPalette();
    },
};
</script>
