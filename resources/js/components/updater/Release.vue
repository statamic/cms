<template>
    <div class="card update-release mb-10">
        <div class="mb-6 flex justify-between">
            <div>
                <h1>{{ release.version }}</h1>
                <h5 class="date" v-text="__('Released on :date', { date })" />
            </div>
            <div v-if="showActions">
                <button
                    class="btn"
                    :disabled="release.type === 'current'"
                    v-text="installButtonText"
                    @click="confirmationPrompt = release"
                />
            </div>
        </div>
        <div class="card-body">
            <div v-html="body"></div>
        </div>

        <confirmation-modal
            v-if="confirmationPrompt"
            :buttonText="__('OK')"
            :cancellable="false"
            @confirm="confirmationPrompt = null"
        >
            <div class="prose">
                <p v-text="confirmationText" />
                <code-block copyable :text="command" />
                <p v-html="link"></p>
            </div>
        </confirmation-modal>
    </div>
</template>

<script>
import DateFormatter from '@statamic/components/DateFormatter.js';

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
            return DateFormatter.format(this.release.date, {
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
            });
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
                    link: `<a href="https://statamic.dev/updating" target="_blank">${__('Updates')}</a>`,
                }) + '.'
            );
        },
    },
};
</script>
