<script>
import GlobalSiteSelector from '../components/GlobalSiteSelector.vue';
import Login from '../components/login/Login.vue';
import TwoFactorChallenge from '../components/login/TwoFactorChallenge.vue';
import EnableTwoFactorAuthentication from '../components/login/EnableTwoFactorAuthentication.vue';
import BaseEntryCreateForm from '../components/entries/BaseCreateForm.vue';
import BaseTermCreateForm from '../components/terms/BaseCreateForm.vue';
import CreateTermButton from '../components/terms/CreateTermButton.vue';
import FieldsetListing from '../components/fieldsets/Listing.vue';
import FieldsetEditForm from '../components/fieldsets/EditForm.vue';
import BlueprintListing from '../components/blueprints/Listing.vue';
import BlueprintBuilder from '../components/blueprints/Builder.vue';
import FormListing from '../components/forms/Listing.vue';
import FormSubmissionListing from '../components/forms/SubmissionListing.vue';
import GlobalListing from '../components/globals/Listing.vue';
import GlobalPublishForm from '../components/globals/PublishForm.vue';
import UserListing from '../components/users/Listing.vue';
import UserWizard from '../components/users/Wizard.vue';
import RoleListing from '../components/roles/Listing.vue';
import RolePublishForm from '../components/roles/PublishForm.vue';
import UserGroupListing from '../components/user-groups/Listing.vue';
import UserGroupPublishForm from '../components/user-groups/PublishForm.vue';
import CollectionScaffolder from '../components/collections/Scaffolder.vue';
import CollectionView from '../components/collections/View.vue';
import CollectionBlueprintListing from '../components/collections/BlueprintListing.vue';
import SessionExpiry from '../components/SessionExpiry.vue';
import NavigationListing from '../components/navigation/Listing.vue';
import PreferencesEditForm from '../components/preferences/EditForm.vue';
import NavigationView from '../components/navigation/View.vue';
import TaxonomyBlueprintListing from '../components/taxonomies/BlueprintListing.vue';
import Updater from '../components/updater/Updater.vue';
import PortalTargets from '../components/portals/PortalTargets.vue';
import SitesEditForm from '../components/sites/EditForm.vue';
import CommandPalette from '../components/command-palette/CommandPalette.vue';
import ItemActions from '../components/actions/ItemActions.vue';
import BulkActions from '../components/actions/BulkActions.vue';

import { defineAsyncComponent } from 'vue';
import { ConfigProvider } from 'reka-ui';

export default {
    components: {
        CommandPalette,
        GlobalSiteSelector,
        Login,
        TwoFactorChallenge,
        EnableTwoFactorAuthentication,
        BaseEntryCreateForm,
        BaseTermCreateForm,
        CreateTermButton,
        FieldsetListing,
        FieldsetEditForm,
        BlueprintListing,
        BlueprintBuilder,
        FormListing,
        FormSubmissionListing,
        GlobalListing,
        GlobalPublishForm,
        UserListing,
        UserWizard,
        RoleListing,
        RolePublishForm,
        UserGroupListing,
        UserGroupPublishForm,
        CollectionScaffolder,
        CollectionView,
        CollectionBlueprintListing,
        SessionExpiry,
        NavigationListing,
        PreferencesEditForm,
        NavigationView,
        TaxonomyBlueprintListing,
        NavBuilder: defineAsyncComponent(() => import('../components/nav/Builder.vue')),
        Updater,
        PortalTargets,
        SitesEditForm,
        ConfigProvider,
        ItemActions,
        BulkActions,
    },

    data() {
        return {
            navOpen: true,
            mobileNavOpen: false,
            showBanner: true,
            appendedComponents: Statamic.$components.components,
            isLicensingBannerSnoozed: localStorage.getItem(`statamic.snooze_license_banner`) > new Date().valueOf(),
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
        this.$keys.bind(['command+\\'], (e) => {
            e.preventDefault();
            this.toggleNav();
        });

        if (this.$config.get('broadcasting.enabled')) {
            this.$echo.start();
        }

        this.fixAutofocus();

        this.showBanner = !this.isLicensingBannerSnoozed && Statamic.$config.get('hasLicenseBanner');

        this.$toast.registerInterceptor(this.$axios);
        this.$toast.displayInitialToasts();
    },

    created() {
        const app = this;
        const state = localStorage.getItem('statamic.nav') || 'open';
        this.navOpen = state === 'open';

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
        toggleNav() {
            this.navOpen = !this.navOpen;
            localStorage.setItem('statamic.nav', this.navOpen ? 'open' : 'closed');
        },

        toggleMobileNav() {
            this.mobileNavOpen = !this.mobileNavOpen;
        },

        hideBanner() {
            this.showBanner = false;
            localStorage.setItem(`statamic.snooze_license_banner`, new Date(Date.now() + 5 * 60 * 1000).valueOf());
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
    },
};
</script>
