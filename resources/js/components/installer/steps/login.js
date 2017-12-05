module.exports = {

    template: require('./login.template.html'),

    props: ['userId'],

    data: function() {
        return {
            loggedIn: false
        }
    },

    ready: function() {
        this.$http.post(this.$parent.url('login'), {
            id: this.userId
        }).success(function(response) {
            if (response.success) {
                this.loggedIn = true;

                setTimeout(function() {
                    this.$dispatch('login.complete');
                }.bind(this), 1000);
            }
        });
    }

};
