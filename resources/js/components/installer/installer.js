module.exports = {

    components: {
        'permissions': require('./steps/permissions'),
        'license-key': require('./steps/licensekey'),
        'settings': require('./steps/settings'),
        'user': require('./steps/user'),
        'login': require('./steps/login')
    },

    props: ['cpUrl'],

    data: function() {
        return {
            steps: {
                'permissions': { label: 'Permissions', status: 'pending' },
                'licenseKey': { label: 'License Key', status: 'pending' },
                'settings': { label: 'Settings', status: 'pending' },
                'user': { label: 'Create a User', status: 'pending' },
                'login': { label: 'Log in', status: 'pending' }
            },
            currentStep: 'permissions',
            userId: null,
            cleanupFailed: false
        }
    },

    methods: {
        complete: function() {
            this.$http.get('/installer/complete').success(function(response) {
                if (response.success) {
                    window.location = this.cpUrl;
                } else {
                    this.cleanupFailed = true;
                }
            });
        },

        url: function(url) {
            // grab the cp root, and pop off the last segment (usually `cp`)
            var root = Statamic.cpRoot.split('/');
            root.pop();
            
            return root.join('/') + '/installer/' + url;
        }
    },

    events: {
        'permissions.status': function(status) {
            this.steps.permissions.status = status;
        },
        'permissions.complete': function() {
            this.currentStep = 'licenseKey';
        },
        'licensekey.complete': function() {
            this.steps.licenseKey.status = 'success';
            this.currentStep = 'settings';
        },
        'settings.complete': function() {
            this.steps.settings.status = 'success';
            this.currentStep = 'user';
        },
        'user.complete': function(id) {
            this.steps.user.status = 'success';
            this.userId = id;
            this.currentStep = 'login';
        },
        'login.complete': function() {
            this.complete();
        }
    },

    ready: function() {
        //
    }

};
