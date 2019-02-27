module.exports = function (vue) {
    return function (scribe) {
        var insertAssetCommand = new scribe.api.Command('insertAsset');

        insertAssetCommand.nodeName = 'A';

        insertAssetCommand.execute = function () {
            var selection = new scribe.api.Selection();
            selection.placeMarkers();

            // hack to prevent the markers from disappearing
            // see: https://github.com/guardian/scribe/issues/199#issuecomment-95259176
            scribe._skipFormatters = true;

            vue.addAsset();
        }

        // whether its currently applied
        insertAssetCommand.queryState = function () {
            // Taken from https://github.com/guardian/scribe-plugin-link-prompt-command
            // It's just checking that we're in an anchor.
            var selection = new scribe.api.Selection();
            return !! selection.getContaining(node => {
                return node.nodeName === this.nodeName;
            });
        }

        insertAssetCommand.queryEnabled = function () {
            return true;
        };

        scribe.commands.insertAsset = insertAssetCommand;
    };
};
