define(function () {

  'use strict';

  return function () {
    return function (scribe) {
      var createLinkCommand = new scribe.api.CommandPatch('createLink');
      scribe.commandPatches.createLink = createLinkCommand;

      createLinkCommand.execute = function (value) {
        var selection = new scribe.api.Selection();

        /**
         * make sure we're not touching any none Scribe elements
         * in the page
         */
        if (!selection.isInScribe()) {
          return;
        }

        /**
         * Firefox does not create a link when selection is collapsed
         * so we create it manually. http://jsbin.com/tutufi/2/edit?js,output
         */
        // using range.collapsed vs selection.isCollapsed - https://code.google.com/p/chromium/issues/detail?id=447523
        if (selection.range.collapsed) {
          var aElement = document.createElement('a');
          aElement.setAttribute('href', value);
          aElement.textContent = value;

          selection.range.insertNode(aElement);

          // Select the created link
          var newRange = document.createRange();
          newRange.setStartBefore(aElement);
          newRange.setEndAfter(aElement);

          selection.selection.removeAllRanges();
          selection.selection.addRange(newRange);
        } else {
          scribe.api.CommandPatch.prototype.execute.call(this, value);
        }
      };
    };
  };

});
