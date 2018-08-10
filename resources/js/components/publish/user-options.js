module.exports = {

    template: require('./user-options.template.html'),

    props: ['username', 'status'],

    methods: {

        sendResetEmail: function() {
            var error = translate('cp.password_reset_email_not_sent');

            this.$http.get(cp_url('users/'+this.username+'/send-reset-email')).success(function (data) {
                if (data.success) {
                    this.$dispatch('setFlashSuccess', translate('cp.email_sent'))
                } else {
                    this.$dispatch('setFlashError', error)
                }
            }).error(function (data) {
                this.$dispatch('setFlashError', error)
            });
        },

        copyResetLink: function() {
            var error = translate('cp.copy_password_reset_link_failed');

            this.$http.get(cp_url('users/'+this.username+'/reset-url')).success(function (data) {
                if (data.success) {
                    prompt('', data.url);
                } else {
                    this.$dispatch('setFlashError', error)
                }
            }).error(function (data) {
                this.$dispatch('setFlashError', error)
            });

        }

    }

};
