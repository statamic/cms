define(function () {

  'use strict';

  return function (api, scribe) {
    function SimpleCommand(commandName, nodeName) {
      scribe.api.Command.call(this, commandName);

      this._nodeName = nodeName;
    }

    SimpleCommand.prototype = Object.create(api.Command.prototype);
    SimpleCommand.prototype.constructor = SimpleCommand;

    SimpleCommand.prototype.queryState = function () {
      var selection = new scribe.api.Selection();
      return scribe.api.Command.prototype.queryState.call(this) && !! selection.getContaining(function (node) {
        return node.nodeName === this._nodeName;
      }.bind(this));
    };

    return SimpleCommand;
  };

});
