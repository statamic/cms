<script>
import Toast from '../mixins/Toast.js';
import { useToast } from 'vue-toastification';

export default {
    mixins: [Toast],
    setup() {
        const toast = useToast()

        return {
            toast
        }
    },
    data() {
        return {
            navOpen: true,
            mobileNavOpen: false,
            showBanner: true,
            appendedComponents: [],
        };
    },
    computed: {
        version() {
            return Statamic.$config.get('version');
        },
        wrapperClass() {
            return this.$config.get('wrapperClass', 'max-w-xl');
        }
    },
    mounted() {
        // this.$toast = toast;

        this.bindWindowResizeListener();

        this.$keys.bind(['command+\\'], e => {
            e.preventDefault();
            this.toggleNav();
        });

        if (this.$config.get('broadcasting.enabled')) {
            this.$echo.start();
        }

        this.fixAutofocus();

        // @todo(jelleroorda): put backs.
        this.showBanner = false // Statamic.$config.get('hasLicenseBanner');
    },
    created() {
        const state = localStorage.getItem('statamic.nav') || 'open';
        this.navOpen = state === 'open';

        Statamic.$callbacks.add('copyToClipboard', async function (url) {
            try {
                await navigator.clipboard.writeText(url);

                Statamic.$toast.success(__('Copied to clipboard'));
            } catch (err) {
                await alert(url);
            }
        });

        Statamic.$callbacks.add('bustAndReloadImageCaches', function (urls) {
            urls.forEach(async url => {
                await fetch(url, { cache: 'reload', mode: 'no-cors' });
                document.body
                    .querySelectorAll(`img[src='${url}']`)
                    .forEach(img => img.src = url);
            });
        });

        this.setupMoment();
    },

    methods: {
        bindWindowResizeListener() {
            window.addEventListener('resize', () => {
                this.$store.commit('statamic/windowWidth', document.documentElement.clientWidth);
            });
            window.dispatchEvent(new Event('resize'));
        },

        toggleNav() {
            this.navOpen = !this.navOpen;
            localStorage.setItem('statamic.nav', this.navOpen ? 'open' : 'closed');
        },

        toggleMobileNav() {
            this.mobileNavOpen = !this.mobileNavOpen;
        },

        hideBanner() {
            this.showBanner = false;
        },

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

        setupMoment() {
            const locale = Statamic.$config.get('locale');

            window.moment.locale(locale);
            this.$moment.locale(locale);

            const spec = {
                relativeTime: {
                    future: __('moment.relativeTime.future'),
                    past: __('moment.relativeTime.past'),
                    s: __('moment.relativeTime.s'),
                    ss: __('moment.relativeTime.ss'),
                    m: __('moment.relativeTime.m'),
                    mm: __('moment.relativeTime.mm'),
                    h: __('moment.relativeTime.h'),
                    hh: __('moment.relativeTime.hh'),
                    d: __('moment.relativeTime.d'),
                    dd: __('moment.relativeTime.dd'),
                    M: __('moment.relativeTime.M'),
                    MM: __('moment.relativeTime.MM'),
                    y: __('moment.relativeTime.y'),
                    yy: __('moment.relativeTime.yy'),
                }
            };

            window.moment.updateLocale(locale, spec);
            this.$moment.updateLocale(locale, spec);
        }
    }
};
</script>
