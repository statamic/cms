define(function () {

  'use strict';

  return function () {
    return function (scribe) {
      var subscriptCommand = new scribe.api.Command('subscript');

      scribe.commands.subscript = subscriptCommand;
    };
  };

});
