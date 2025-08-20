<template>
    <ui-panel>
        <ui-panel-header class="flex items-center justify-between">
            <div>
                <ui-heading :text="release.version" />
                <ui-subheading :text="`${__('Released on :date', { date })}`" />
            </div>
            <ui-modal :title="__('Update to :version', { version: release.version })">
                <template #trigger>
                    <ui-button
                        v-if="showActions"
                        icon="clipboard"
                        size="sm"
                        :disabled="release.type === 'current'"
                        :text="__('Get Command')"
                    />
                </template>
                <div class="prose prose-sm prose-zinc dark:prose-invert prose-headings:font-medium space-y-3">
                    <p v-text="confirmationText" />
                    <ui-input v-model="command" readonly copyable class="font-mono text-sm dark" />
                    <p v-html="link" />
                </div>
            </ui-modal>
        </ui-panel-header>
        <ui-card>
            <div v-html="body" class="prose prose-sm prose-zinc dark:prose-invert prose-headings:font-medium" />
        </ui-card>
    </ui-panel>
</template>

<script>
import DateFormatter from '@/components/DateFormatter.js';

export default {
    props: {
        release: { type: Object, required: true },
        package: { type: String, required: true },
        packageName: { type: String, required: true },
        showActions: { type: Boolean },
    },

    data() {
        return {
            confirmationPrompt: null,
        };
    },

    computed: {
        date() {
            return DateFormatter.format(this.release.date, 'date');
        },

        body() {
            return markdown(this.release.body)
                .replaceAll('[new]', '<span class="label" style="background: #5bc0de;">NEW</span>')
                .replaceAll('[fix]', '<span class="label" style="background: #5cb85c;">FIX</span>')
                .replaceAll('[break]', '<span class="label" style="background: #d9534f;">BREAK</span>')
                .replaceAll('[na]', '<span class="label" style="background: #e8e8e8;">N/A</span>');
        },

        installButtonText() {
            if (this.release.type === 'current') {
                return __('Current Version');
            }

            if (this.release.latest) {
                return __('Update to Latest');
            }

            if (this.release.type === 'upgrade') {
                return __('Update to :version', { version: this.release.version });
            }

            return __('Downgrade to :version', { version: this.release.version });
        },

        confirmationText() {
            if (this.release.latest) {
                return `${__('messages.updater_update_to_latest_command')}:`;
            }

            return `${__('messages.updater_require_version_command')}:`;
        },

        command() {
            if (this.release.latest) {
                return `composer update ${this.package}`;
            }

            return `composer require "${this.package} ${this.release.version}"`;
        },

        link() {
            return (
                __('Learn more about :link', {
                    link: `<a href="https://statamic.dev/updating" target="_blank" class="font-medium underline text-blue-500 dark:text-blue-400">${__('updating Statamic')}</a>`,
                }) + '.'
            );
        },
    },
};
</script>
