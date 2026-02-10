<script>
export default {
    components: {
    },

    data() {
        return {
            appendedComponents: Statamic.$components.components,
            copyToClipboardModalUrl: null,
        };
    },

    computed: {
        version() {
            return Statamic.$config.get('version');
        },

        stackCount() {
            return this.$stacks.count();
        },
    },

    mounted() {
        if (this.$config.get('broadcasting.enabled')) {
            this.$echo.start();
        }

        this.fixAutofocus();

        this.$toast.registerInterceptor(this.$axios);
        this.$toast.displayInitialToasts();
    },

    created() {
        const app = this;

        Statamic.$callbacks.add('copyToClipboard', async function (url) {
            try {
                await navigator.clipboard.writeText(url);
                Statamic.$toast.success(__('Copied to clipboard'));
            } catch (err) {
                app.copyToClipboardModalUrl = url;
            }
        });

        Statamic.$callbacks.add('bustAndReloadImageCaches', function (urls) {
            urls.forEach(async (url) => {
                await fetch(url, { cache: 'reload', mode: 'no-cors' });
                document.body.querySelectorAll(`img[src='${url}']`).forEach((img) => (img.src = url));
            });
        });
    },

    methods: {

        fixAutofocus() {
            // Fix autofocus issues in Safari and Firefox
            setTimeout(() => {
                const inputs = document.querySelectorAll('input[autofocus]');
                for (let input of inputs) {
                    input.blur();
                }
                if (inputs.length) {
                    inputs[0].focus();
                }
            }, 100);
        },
    },
};
</script>
