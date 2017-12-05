module.exports = {

    template: require('./licensekey.template.html'),

    data: function() {
        return {
            loading: true,
            valid: false,
            key: null,
            keySubmitted: false
        }
    },

    computed: {
        licenseKeyInstructions: function() {
            return translate('cp.license_key_instructions', {
              'licenses': 'https://account.statamic.com/licenses',
              'buy_license': 'https://store.statamic.com'
            });
        }
    },

    methods: {
        continue: function() {
            this.$dispatch('licensekey.complete');
        },

        validate: function() {
            this.loading = true;

            this.$http.post(this.$parent.url('license'), {
                key: this.key
            }).success(function(response) {
                this.success = response.success
                this.key = response.key;
                this.loading = false;

                if (response.key) {
                    this.keySubmitted = true;
                }

                if (this.success) {
                    this.valid = true;
                    this.$dispatch('licensekey.status', 'success');

                    setTimeout(function() {
                        this.continue();
                    }.bind(this), 1000);
                }
            });
        }
    },

    ready: function() {
        this.validate();
    }

};
