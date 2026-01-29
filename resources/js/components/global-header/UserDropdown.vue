<script setup>
import { computed } from 'vue';
import { Avatar, Button, DropdownHeader, Badge, Dropdown, DropdownMenu, DropdownItem, DropdownFooter, ToggleGroup, ToggleItem } from '@ui';
import useStatamicPageProps from '@/composables/page-props.js';

const { supportUrl } = useStatamicPageProps();
const logoutUrl = `${cp_url('auth/logout')}?redirect=${cp_url('/')}`;
const user = Statamic.user;
const isImpersonating = computed((() => user.is_impersonating));
</script>

<template>
    <Dropdown align="end" v-cloak>
        <template #trigger>
            <Button :icon-only="true" variant="ghost">
                <Avatar :user="user" />
            </Button>
        </template>

        <DropdownHeader>
            <div class="flex items-center gap-2">
                <Avatar :user="user" class="size-8" />
                <div>
                    <div class="text-sm" v-text="user.email" />
                    <div v-if="user.super" class="text-xs text-gray-500 font-normal flex items-center gap-1">
                        {{ __('Super Admin') }}
                        <Badge v-if="isImpersonating" size="sm" pill :text="__('Impersonating')" />
                    </div>
                    <Badge v-else-if="isImpersonating" size="sm" pill :text="__('Impersonating')" />
                </div>
            </div>
        </DropdownHeader>

        <DropdownMenu>
            <DropdownItem
                :href="cp_url('account')"
                icon="avatar"
                :text="__('Manage profile')"
            />
            <DropdownItem
                :href="cp_url('preferences/edit')"
                icon="cog"
                :text="__('Preferences')"
            />
            <DropdownItem
                v-if="supportUrl"
                :href="supportUrl"
                icon="support"
                :text="__('Get support')"
                target="_blank"
            />
            <DropdownItem
                v-if="isImpersonating"
                :href="cp_url('auth/stop-impersonating')"
                icon="mask"
                :text="__('Stop impersonating')"
            />
            <DropdownItem
                as="a"
                :href="logoutUrl"
                icon="sign-out"
                :text="__('Sign out')"
            />
        </DropdownMenu>

        <DropdownFooter class="px-1.75! space-y-2">
            <ToggleGroup variant="ghost" size="xs" class="justify-between" v-model="$colorMode.preference">
                <ToggleItem icon="sun" class="[&_svg]:size-4.5" value="light" :label="__('Light')" />
                <ToggleItem icon="moon" class="[&_svg]:size-4.5" value="dark" :label="__('Dark')" />
                <ToggleItem icon="monitor" class="[&_svg]:size-4.5" value="auto" :label="__('System')" />
            </ToggleGroup>
        </DropdownFooter>
    </Dropdown>

</template>
