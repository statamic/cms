module.exports = {

    props: {
        versionTo: { type: String, default: null },
        versionFrom: { type: String, default: null }
    },

    data() {
        return {
            started: false,

            backingUp: false,
            backedUp: false,
            backupFailed: false,
            backupMessage: null,

            downloading: false,
            downloaded: false,
            downloadFailed: false,
            downloadMessage: null,

            installing: false,

            unzipping: false,
            unzipped: false,
            unzippingFailed: false,

            installingDependencies: false,
            installedDependencies: false,
            installingDependenciesFailed: false,

            swapping: false,
            swapped: false,
            swappingFailed: false,

            updated: false,
            cleaningUp: false,
            cleanedUp: false,
            cleanupFailed: false,

            errors: []
        }
    },

    computed: {
        readyToInstall() {
            return this.backedUp && this.downloaded;
        },

        hasErrors() {
            return this.errors.length > 0;
        }
    },

    watch: {
        updated(updated) {
            if (updated) {
                this.$els.audio.play();
            }
        }
    },

    methods: {
        start() {
            this.started = true;
            this.backup();
            this.download();
        },

        backup() {
            this.backingUp = true
            this.$http.post(cp_url('system/updater/backup')).success(function (data) {
                this.backingUp = false
                this.backedUp = true
                this.backupMessage = data.message
                this.install()
            }).error(function (data) {
                this.backingUp = false
                this.backupFailed = true
                this.womp(data)
            })
        },

        download() {
            this.downloading = true;
            this.$http.post(cp_url('system/updater/download'), { version: this.versionTo }).success(function (data) {
                this.downloading = false
                this.downloaded = true
                this.downloadMessage = data.message
                this.install()
            }).error(function (data) {
                this.downloading = false
                this.downloadFailed = true
                this.womp(data)
            });
        },

        install() {
            if (this.readyToInstall) {
                this.installing = true
                this.unzip()
            }
        },

        unzip() {
            this.unzipping = true;
            this.$http.post(cp_url('system/updater/unzip'), { version: this.versionTo }).success(function () {
                this.unzipping = false
                this.unzipped = true
                this.composer()
            }).error(function (data) {
                this.unzipping = false
                this.unzipped = false
                this.unzippingFailed = true
                this.womp(data)
            });
        },

        composer() {
            this.installingDependencies = true;
            this.$http.post(cp_url('system/updater/composer')).success(function () {
                this.installingDependencies = false
                this.installedDependencies = true
                this.swap()
            }).error(function (data) {
                this.installingDependencies = false
                this.installingDependenciesFailed = false
                this.womp(data)
            });
        },

        swap() {
            this.swapping = true;
            this.$http.post(cp_url('system/updater/swap')).success(function () {
                this.swapping = false
                this.swapped = true
                this.cleanUp()
            }).error(function (data) {
                this.swapping = false
                this.swappingFailed = true
                this.womp(data)
            });
        },

        cleanUp: function() {
            this.updated = true
            this.cleaningUp = true

            // update version number
            this.$root.version = this.version;
            $('.nav-main .update').hide();

            this.$http.post(cp_url('system/updater/clean'), {
                version: this.versionTo,
                oldVersion: this.versionFrom
            }).success(function () {
                this.cleaningUp = false
                this.cleanedUp = true
            }).error(function (data) {
                this.cleaningUp = false
                this.cleanUpFailed = true
                this.womp(data)
            })
        },

        womp: function(data) {
            var self = this

            _.each(data.errors, function (error) {
                self.errors.push(error)
            })
        }
    }
};
