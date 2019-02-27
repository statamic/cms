define(function () {

  /**
   * Prevent Chrome from removing formatting of BLOCKQUOTE contents.
   */

  'use strict';

  return function () {
    return function (scribe) {
      var nodeHelpers = scribe.node;
      var outdentCommand = new scribe.api.CommandPatch('outdent');

      outdentCommand.execute = function () {
        scribe.transactionManager.run(function () {
          var selection = new scribe.api.Selection();
          var range = selection.range;

          var blockquoteNode = selection.getContaining(function (node) {
            return node.nodeName === 'BLOCKQUOTE';
          });

          if (range.commonAncestorContainer.nodeName === 'BLOCKQUOTE') {
            /**
             * Chrome: Applying the outdent command when a whole BLOCKQUOTE is
             * selected removes the formatting of its contents.
             * As per: http://jsbin.com/okAYaHa/1/edit?html,js,output
             */

            // Insert a copy of the selection before the BLOCKQUOTE, and then
            // restore the selection on the copy.
            selection.placeMarkers();
            // We want to copy the selected nodes *with* the markers
            selection.selectMarkers(true);
            var selectedNodes = range.cloneContents();
            blockquoteNode.parentNode.insertBefore(selectedNodes, blockquoteNode);
            range.deleteContents();
            selection.selectMarkers();

            // Delete the BLOCKQUOTE if it's empty
            if (blockquoteNode.textContent === '') {
              blockquoteNode.parentNode.removeChild(blockquoteNode);
            }
          } else {
            /**
             * Chrome: If we apply the outdent command on a P, the contents of the
             * P will be outdented instead of the whole P element.
             * As per: http://jsbin.com/IfaRaFO/1/edit?html,js,output
             */

            var pNode = selection.getContaining(function (node) {
              return node.nodeName === 'P';
            });

            if (pNode) {
              /**
               * If we are not at the start of end of a BLOCKQUOTE, we have to
               * split the node and insert the P in the middle.
               */

              var nextSiblingNodes = nodeHelpers.nextSiblings(pNode);

              if (!!nextSiblingNodes.size) {
                var newContainerNode = document.createElement(blockquoteNode.nodeName);

                while (!!nextSiblingNodes.size) {
                  newContainerNode.appendChild(nextSiblingNodes.first());
                  nextSiblingNodes = nextSiblingNodes.shift();
                }

                blockquoteNode.parentNode.insertBefore(newContainerNode, blockquoteNode.nextElementSibling);
              }

              selection.placeMarkers();
              blockquoteNode.parentNode.insertBefore(pNode, blockquoteNode.nextElementSibling);
              selection.selectMarkers();

              // If the BLOCKQUOTE is now empty, clean it up.
              if (blockquoteNode.innerHTML === '') {
                blockquoteNode.parentNode.removeChild(blockquoteNode);
              }
            } else {
              scribe.api.CommandPatch.prototype.execute.call(this);
            }
          }
        }.bind(this));
      };

      scribe.commandPatches.outdent = outdentCommand;
    };
  };

});
