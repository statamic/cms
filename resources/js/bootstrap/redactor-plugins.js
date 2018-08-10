$.Redactor.prototype.assets = function () {

    return {

        init: function () {
            // If assets aren't enabled, don't add the button.
            if (! this.assets.vue().assetsEnabled) {
                return;
            }

            var button = this.button.add('assets', translate_choice('cp.assets', 2));
            this.button.addCallback(button, this.assets.show);
        },

        show: function () {
            this.selection.save();
            this.assets.vue().addAsset();
        },

        vue: function () {
            return this.$editor.closest('.redactor-fieldtype-wrapper')[0].__vue__;
        }

    }

};
