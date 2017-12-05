module.exports = {

    template: require('./user.template.html'),

    data: function() {
        return {
            submitted: false,
            loading: false,
            success: null,
            username: null,
            password: null,
            email: null,
            firstName: null,
            lastName: null,
            bio: null
        }
    },

    computed: {
        submitDisabled: function() {
            if (this.loading) return true;

            if (!this.username || !this.password || !this.email || !this.firstName || !this.lastName) {
                return true;
            }

            return false;
        }
    },

    methods: {
        submit: function() {
            this.loading = true;
            this.submitted = true;

            this.$http.post(this.$parent.url('user'), {
                username: this.username,
                password: this.password,
                email: this.email,
                first_name: this.firstName,
                last_name: this.lastName,
                bio: this.bio,
            }).success(function(response) {
                this.loading = false;
                this.success = response.success;

                if (this.success) {
                    setTimeout(function() {
                        this.$dispatch('user.complete', response.id);
                    }.bind(this), 1000);
                }
                this.login(response.id);
            });
        },

        login: function(id) {
            this.loggingIn = true;

            this.$http.post(this.$parent.url('login'), {
                id: id
            }).success(function(response) {
                this.loggedIn = true;

                if (response.success) {
                    setTimeout(function() {
                        this.$dispatch('user.complete');
                    }.bind(this), 1000);
                }
            });
        }
    }

};
