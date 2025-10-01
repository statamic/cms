<script setup>
import { Modal, Description, Button } from '@/components/ui';
import { computed, ref } from 'vue';
import useStatamicPageProps from '@/composables/page-props.js';

const { licensing } = useStatamicPageProps();
const { alert } = licensing;
const message = ref(alert?.message);
const testing = ref(alert?.testing);
const manageUrl = ref(alert?.manageUrl);
const key = 'statamic.snooze_license_banner';
const open = ref(localStorage.getItem(key) < new Date().valueOf());
const snoozeMinutes = computed(() => testing.value ? (24 * 60) : 5);
const snoozeMilliseconds = computed(() => snoozeMinutes.value * 60 * 1000);

function snooze() {
    open.value = false;
    localStorage.setItem(key, new Date(Date.now() + snoozeMilliseconds.value).valueOf());
}

function manageLicenses() {
    snooze();
    window.location = manageUrl.value;
}
</script>

<template>
    <Modal
        v-if="alert"
        :title="__('Licensing Alert')"
        :open="open"
        @update:open="snooze"
        icon="alert-alarm-bell"
        class="[&_[data-ui-heading]]:text-red-700! [&_svg]:text-red-700! dark:[&_[data-ui-heading]]:text-red-400! dark:[&_svg]:text-red-400!'"
        :dismissible="false"
    >
        <div class="flex items-center justify-between">
            <Description :text="message" />
        </div>
        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <Button @click="snooze" :text="__('Snooze')" variant="ghost" tabindex="-1" />
                <Button v-if="manageUrl" @click="manageLicenses" :text="__('Manage Licenses')" />
            </div>
        </template>
    </Modal>
</template>
