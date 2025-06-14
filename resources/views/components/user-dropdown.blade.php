<ui-dropdown align="end" x-cloak>
    <template #trigger>
        <ui-button :icon-only="true" variant="ghost">
            <img src="{{ $user->avatar() }}" class="size-7 rounded-full [button:has(&)]:rounded-full" alt="{{ $user->name() }}" />
        </ui-button>
    </template>

    <ui-dropdown-header>
        <div class="flex items-center gap-2">
            <img src="{{ $user->avatar() }}" class="size-8 rounded-full" alt="{{ $user->name() }}" />
            <div>
                <div class="text-sm">{{ $user->email() }}</div>
                @if ($user->isSuper())
                    <div class="text-xs text-gray-500 font-normal flex items-center gap-1">
                        {{ __('Super Admin') }}
                        @if (session()->get('statamic_impersonated_by'))
                            <ui-badge size="sm" pill :text="__('Impersonating')" />
                        @endif
                    </div>
                @elseif (session()->get('statamic_impersonated_by'))
                    <ui-badge size="sm" pill :text="__('Impersonating')" />
                @endif
            </div>
        </div>
    </ui-dropdown-header>

    <ui-dropdown-menu>
        <ui-dropdown-item
            href="{{ route('statamic.cp.account') }}"
            icon="avatar"
            :text="__('Manage profile')"
        />
        <ui-dropdown-item
            href="{{ cp_route('preferences.user.edit') }}"
            icon="cog"
            :text="__('Preferences')"
        />
        @if (config('statamic.cp.support_url'))
            <ui-dropdown-item
                href="{{ config('statamic.cp.support_url') }}"
                icon="support"
                :text="__('Get support')"
                target="_blank"
            >
            </ui-dropdown-item>
        @endif
        @if (session()->get('statamic_impersonated_by'))
            <ui-dropdown-item
                href="{{ cp_route('impersonation.stop') }}"
                icon="mask"
                :text="__('Stop impersonating')"
            />
        @endif
        <ui-dropdown-item
            href="{{ route('statamic.cp.logout', ['redirect' => cp_route('index')]) }}"
            icon="sign-out"
            :text="__('Sign out')"
        />
    </ui-dropdown-menu>

    <ui-dropdown-footer class="px-1.75!">
        <ui-toggle-group variant="ghost" size="xs" class="justify-between" v-model="$theme.preference">
            <ui-toggle-item icon="sun" class="[&_svg]:size-4.5" value="light" :label="__('Light')" />
            <ui-toggle-item icon="moon" class="[&_svg]:size-4.5" value="dark" :label="__('Dark')" />
            <ui-toggle-item icon="monitor" class="[&_svg]:size-4.5" value="auto" :label="__('System')" />
        </ui-toggle-group>
    </ui-dropdown-footer>
</ui-dropdown>
