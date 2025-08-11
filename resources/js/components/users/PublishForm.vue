<template>
    <div>
        <Header :title="title" icon="users">
            <ItemActions
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
                        <Button icon="ui/dots" variant="ghost" :aria-label="__('Open dropdown menu')" />
                    </template>
                    <DropdownMenu>
                        <DropdownItem :text="__('Edit Blueprint')" icon="blueprint-edit" v-if="canEditBlueprint" :href="actions.editBlueprint" />
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

            <Button variant="primary" @click.prevent="save" v-text="__('Save')" />

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
            <PublishTabs>
                <template #settings="{ tab }">
                    <div v-if="needsToConfirmPassword" class="py-24 text-center space-y-4">
                        <p>Before doing anything, you need to confirm your password.</p>
                        <Button @click="confirmPassword">Confirm your password</Button>
                    </div>

                    <template v-else>
                        <PublishSections :tab="tab" />

                        <Panel>
                            <PanelHeader title="Two Factor Authentication" />
                            <Card>
                                <TwoFactor v-if="twoFactor" v-bind="twoFactor" />
                            </Card>
                        </Panel>

                        <Panel>
                            <PanelHeader title="Password" />
                            <Card>
                                <change-password
                                    v-if="canEditPassword"
                                    :save-url="actions.password"
                                    :requires-current-password="requiresCurrentPassword"
                                />
                            </Card>
                        </Panel>
                    </template>
                </template>
            </PublishTabs>
        </PublishContainer>
    </div>
</template>

<script>
import ChangePassword from './ChangePassword.vue';
import HasActions from '../publish/HasActions';
import TwoFactor from '@statamic/components/two-factor/TwoFactor.vue';
import clone from '@statamic/util/clone.js';
import resetValuesFromResponse from '@statamic/util/resetValuesFromResponse.js';
import {
    Button,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    DropdownSeparator,
    PublishContainer,
    PublishTabs,
    Header,
    Panel,
    PanelHeader,
    PublishSections,
} from '@statamic/ui';
import ItemActions from '@statamic/components/actions/ItemActions.vue';
import { requireElevatedSession, SavePipeline } from '@statamic/exports.js';
import { computed, ref } from 'vue';
import { Card } from 'statamic';
const { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks, PipelineStopped } = SavePipeline;

let saving = ref(false);
let errors = ref({});
let container = null;

export default {
    mixins: [HasActions],

    components: {
        Card,
        Panel,
        PanelHeader,
        PublishSections,
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

            needsToConfirmPassword: true,
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
                    Statamic.$toast.success('Saved');

                    this.title = response.data.title;

                    this.$nextTick(() => this.$emit('saved', response));
                });
        },

        confirmPassword() {
            requireElevatedSession()
                .then(() => this.needsToConfirmPassword = false)
                .catch(() => {});
        },

        afterActionSuccessfullyCompleted(response) {
            if (response.data) {
                this.title = response.data.title;
                this.values = resetValuesFromResponse(response.data.values, this.$refs.container);
            }
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
    },
};
</script>
