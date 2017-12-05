module.exports = {

    template: require('./permissions.template.html'),

    data: function() {
        return {
            loading: true,
            success: null,
            unwritable: []
        }
    },

    computed: {
        failure: function() {
            return !this.loading && !this.success;
        }
    },

    ready: function() {
        this.$http.get(this.$parent.url('permissions')).success(function(response) {
            this.success = response.success;
            this.unwritable = response.unwritable;
            this.loading = false;

            if (! this.success) {
                this.$dispatch('permissions.status', 'failure');

            } else {
                this.$dispatch('permissions.status', 'success');

                setTimeout(function() {
                    this.$dispatch('permissions.complete');
                }.bind(this), 1000);
            }
        });
    }

};
