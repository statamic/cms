<template>
    <div>
        <Listing
            :url="requestUrl"
            :action-url="actionUrl"
            :action-context="actionContext"
            :preferences-prefix="preferencesPrefix"
            :filters="filters"
            :allow-presets="allowFilterPresets"
            :sort-column="initialSortColumn"
            :sort-direction="initialSortDirection"
            :additional-parameters="additionalParameters"
            push-query
        >
            <template #cell-email="{ row: user }">
                <a class="title-index-field" :href="user.edit_url" @click.stop>
                    <avatar :user="user" class="h-8 w-8 rounded-full ltr:mr-2 rtl:ml-2" />
                    <span v-text="user.email" />
                </a>
            </template>
            <template #cell-roles="{ row: user, value: roles }">
                <div class="role-index-field">
                    <div v-if="user.super" class="role-index-field-item mb-1.5 ltr:mr-1 rtl:ml-1">
                        {{ __('Super Admin') }}
                    </div>
                    <div v-if="!roles || roles.length === 0" />
                    <div v-for="(role, i) in roles || []" class="role-index-field-item mb-1.5 ltr:mr-1 rtl:ml-1">
                        {{ __(role.title) }}
                    </div>
                </div>
            </template>
            <template #cell-groups="{ row: user, value: groups }">
                <div class="groups-index-field">
                    <div v-for="group in groups || []" class="groups-index-field-item mb-1.5 ltr:mr-1 rtl:ml-1">
                        {{ __(group.title) }}
                    </div>
                </div>
            </template>
            <template #cell-two_factor="{ row: user, value }">
                <div class="flex items-center">
                    <ui-icon name="checkmark" class="size-3 text-green-600" v-if="value" />
                    <ui-icon name="x" class="size-3 text-gray-400 dark:text-gray-600" v-else />
                </div>
            </template>
        </Listing>
    </div>
</template>

<script>
import { Listing } from '@/components/ui';

export default {
    components: {
        Listing,
    },

    props: {
        group: String,
        allowFilterPresets: {
            default: true,
        },
        actionUrl: { type: String, default: null },
        filters: { type: Array, default: () => [] },
        initialSortColumn: { type: String, default: 'email' },
        initialSortDirection: { type: String, default: 'asc' },
    },

    data() {
        return {
            preferencesPrefix: 'users',
            requestUrl: cp_url('users'),
        };
    },

    computed: {
        actionContext() {
            return { group: this.group };
        },
        additionalParameters() {
            return {
                group: this.group,
            };
        },
    },
};
</script>
