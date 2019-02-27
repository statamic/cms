define(function () {

  'use strict';

  return function () {
    return function (scribe) {
      var indentCommand = new scribe.api.Command('indent');

      indentCommand.queryEnabled = function () {
        /**
         * FIXME: Chrome nests ULs inside of ULs
         * Currently we just disable the command when the selection is inside of
         * a list.
         * As per: http://jsbin.com/ORikUPa/3/edit?html,js,output
         */
        var selection = new scribe.api.Selection();
        var listElement = selection.getContaining(function (element) {
          return element.nodeName === 'UL' || element.nodeName === 'OL';
        });

        return scribe.api.Command.prototype.queryEnabled.call(this) && scribe.allowsBlockElements() && ! listElement;
      };

      scribe.commands.indent = indentCommand;
    };
  };

});
