module.exports = {

    template: require('./settings.template.html'),

    data: function() {
        return {
            loading: false,
            success: null,
            debug: false,
            locales: [{
                locale: 'en',
                full: 'en_US',
                name: 'English',
                url: document.location.origin+'/'
            }]
        }
    },

    computed: {
        formData: function() {
            return {
                debug: this.debug,
                locales: this.locales
            };
        }
    },

    methods: {
        submit: function() {
            this.loading = true;

            this.$http.post(this.$parent.url('settings'), this.formData).success(function(response) {
                this.loading = false;
                this.success = response.success;

                if (this.success) {
                    setTimeout(function() {
                        this.$dispatch('settings.complete');
                    }.bind(this), 1000);
                }
            });
        }
    }

};
