module.exports = {

    template: require('./user-options.template.html'),

    props: ['username', 'status'],

    methods: {

        sendResetEmail: function() {
            var error = 'Email not sent. Please check your logs.';

            this.$http.get(cp_url('users/'+this.username+'/send-reset-email')).success(function (data) {
                if (data.success) {
                    this.$dispatch('setFlashSuccess', 'Email sent.')
                } else {
                    this.$dispatch('setFlashError', error)
                }
            }).error(function (data) {
                this.$dispatch('setFlashError', error)
            });
        },

        copyResetLink: function() {
            var error = 'There was a problem generating the link. Please check your logs.';

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
