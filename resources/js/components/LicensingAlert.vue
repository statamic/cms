<script setup>
import { Modal, Description, Button } from '@/components/ui';
import { ref } from 'vue';

const props = defineProps({
    message: String,
    variant: String,
    manageUrl: String,
});

const open = ref(
    Statamic.$config.get('hasLicenseBanner')
    && sessionStorage.getItem(`statamic.snooze_license_banner`) !== 'true'
);

function snooze() {
    open.value = false;
    sessionStorage.setItem(`statamic.snooze_license_banner`, 'true');
}

function manageLicenses() {
    snooze();
    window.location = props.manageUrl;
}
</script>

<template>
    <Modal
        :title="__('Licensing Alert')"
        :open="open"
        @update:open="snooze"
        icon="alert-alarm-bell"
        class="[&_[data-ui-heading]]:text-red-700! [&_svg]:text-red-700! dark:[&_[data-ui-heading]]:text-red-400! dark:[&_svg]:text-red-400!'"
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
