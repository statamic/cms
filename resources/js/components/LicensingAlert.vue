<script setup>
import { Modal, Description, Button } from '@/components/ui';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import useStatamicPageProps from '@/composables/page-props.js';
import { router } from '@inertiajs/vue3';

const { licensing } = useStatamicPageProps();
const { alert } = licensing;
const message = ref(alert?.message);
const testing = ref(alert?.testing);
const manageUrl = ref(alert?.manageUrl);
const handoffUrl = ref(alert?.handoffUrl);
const siteUrl = ref(alert?.siteUrl);
const refreshUrl = ref(alert?.refreshUrl);
const mintUrl = ref(alert?.mintUrl);
const primaryAction = ref(alert?.primaryAction);
const minting = ref(false);
const awaitingReturn = ref(false);
const key = 'statamic.snooze_license_banner';
const open = ref(localStorage.getItem(key) < new Date().valueOf());
const snoozeMinutes = computed(() => testing.value ? (24 * 60) : 5);
const snoozeMilliseconds = computed(() => snoozeMinutes.value * 60 * 1000);

function mint() {
    if (!mintUrl.value || minting.value) {
        return;
    }

    minting.value = true;
    router.post(mintUrl.value, {}, {
        onFinish: () => {
            minting.value = false;
        },
    });
}

function snooze() {
    open.value = false;
    localStorage.setItem(key, new Date(Date.now() + snoozeMilliseconds.value).valueOf());
}

function manageLicenses() {
    snooze();
    router.get(manageUrl.value);
}

function markOutbound() {
    awaitingReturn.value = true;
}

function maybeRefresh() {
    if (!awaitingReturn.value || !refreshUrl.value) {
        return;
    }

    awaitingReturn.value = false;
    router.visit(refreshUrl.value);
}

function onVisibilityChange() {
    if (document.visibilityState === 'visible') {
        maybeRefresh();
    }
}

onMounted(() => {
    document.addEventListener('visibilitychange', onVisibilityChange);
    window.addEventListener('focus', maybeRefresh);
});

onUnmounted(() => {
    document.removeEventListener('visibilitychange', onVisibilityChange);
    window.removeEventListener('focus', maybeRefresh);
});
</script>

<template>
    <Modal
        v-if="alert"
        :title="__('Licensing Alert')"
        :open="open"
        blur
        @update:open="snooze"
        icon="alert-alarm-bell"
        class="[&_[data-ui-heading]]:text-red-600! [&_svg]:text-red-600 dark:[&_[data-ui-heading]]:text-red-400! dark:[&_svg]:text-red-400!"
        :dismissible="false"
    >
        <div class="flex items-center justify-between">
            <Description :text="message" />
        </div>
        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <Button @click="snooze" :text="__('Snooze')" variant="ghost" tabindex="-1" />
                <Button v-if="manageUrl" @click="manageLicenses" :text="__('View details')" variant="ghost" />
                <Button
                    v-if="primaryAction === 'mint'"
                    :disabled="minting"
                    variant="primary"
                    :text="__('Generate site key')"
                    @click="mint"
                />
                <Button
                    v-if="primaryAction === 'connect'"
                    :href="handoffUrl"
                    target="_blank"
                    variant="primary"
                    :text="__('Connect to statamic.com')"
                    @click="markOutbound"
                />
                <Button
                    v-if="primaryAction === 'buy' && manageUrl"
                    variant="primary"
                    :text="__('Buy Licenses')"
                    @click="manageLicenses"
                />
                <Button
                    v-if="primaryAction === 'renew' && manageUrl"
                    variant="primary"
                    :text="__('Renew License')"
                    @click="manageLicenses"
                />
                <Button
                    v-if="primaryAction === 'domain'"
                    :href="siteUrl"
                    target="_blank"
                    variant="primary"
                    :text="__('Add domain on statamic.com')"
                    @click="markOutbound"
                />
            </div>
        </template>
    </Modal>
</template>
