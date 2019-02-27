define([
  'immutable'
], function (Immutable) {

  /**
   * If the paragraphs option is set to true, then when the list is
   * unapplied, ensure that we enter a P element.
   */

  'use strict';

  return function () {
    return function (scribe) {
      var nodeHelpers = scribe.node;

      var InsertListCommand = function (commandName) {
        scribe.api.Command.call(this, commandName);
      };

      InsertListCommand.prototype = Object.create(scribe.api.Command.prototype);
      InsertListCommand.prototype.constructor = InsertListCommand;

      InsertListCommand.prototype.execute = function (value) {
        function splitList(listItemElements) {
          if (!!listItemElements.size) {
            var newListNode = document.createElement(listNode.nodeName);

            while (!!listItemElements.size) {
              newListNode.appendChild(listItemElements.first());
              listItemElements = listItemElements.shift();
            }

            listNode.parentNode.insertBefore(newListNode, listNode.nextElementSibling);
          }
        }

        if (this.queryState()) {
          var selection = new scribe.api.Selection();
          var range = selection.range;

          var listNode = selection.getContaining(function (node) {
            return node.nodeName === 'OL' || node.nodeName === 'UL';
          });

          var listItemElement = selection.getContaining(function (node) {
            return node.nodeName === 'LI';
          });

          scribe.transactionManager.run(function () {
            if (listItemElement) {
              var nextListItemElements = nodeHelpers.nextSiblings(listItemElement);

              /**
               * If we are not at the start or end of a UL/OL, we have to
               * split the node and insert the P(s) in the middle.
               */
              splitList(nextListItemElements);

              /**
               * Insert a paragraph in place of the list item.
               */

              selection.placeMarkers();

              var pNode = document.createElement('p');
              pNode.innerHTML = listItemElement.innerHTML;

              listNode.parentNode.insertBefore(pNode, listNode.nextElementSibling);
              listItemElement.parentNode.removeChild(listItemElement);
            } else {
              /**
               * When multiple list items are selected, we replace each list
               * item with a paragraph.
               */

              // We can't query for list items in the selection so we loop
              // through them all and find the intersection ourselves.
              var selectedListItemElements = Immutable.List(listNode.querySelectorAll('li'))
                .filter(function (listItemElement) {
                  return range.intersectsNode(listItemElement);
                });
              var lastSelectedListItemElement = selectedListItemElements.last();
              var listItemElementsAfterSelection = nodeHelpers.nextSiblings(lastSelectedListItemElement);

              /**
               * If we are not at the start or end of a UL/OL, we have to
               * split the node and insert the P(s) in the middle.
               */
              splitList(listItemElementsAfterSelection);

              // Store the caret/range positioning inside of the list items so
              // we can restore it from the newly created P elements soon
              // afterwards.
              selection.placeMarkers();

              var documentFragment = document.createDocumentFragment();
              selectedListItemElements.forEach(function (listItemElement) {
                var pElement = document.createElement('p');
                pElement.innerHTML = listItemElement.innerHTML;
                documentFragment.appendChild(pElement);
              });

              // Insert the Ps
              listNode.parentNode.insertBefore(documentFragment, listNode.nextElementSibling);

              // Remove the LIs
              selectedListItemElements.forEach(function (listItemElement) {
                listItemElement.parentNode.removeChild(listItemElement);
              });
            }

            // If the list is now empty, clean it up.
            if (listNode.childNodes.length === 0) {
              listNode.parentNode.removeChild(listNode);
            }

            selection.selectMarkers();
          }.bind(this));
        } else {
          scribe.api.Command.prototype.execute.call(this, value);
        }
      };

      InsertListCommand.prototype.queryEnabled = function () {
        return scribe.api.Command.prototype.queryEnabled.call(this) && scribe.allowsBlockElements();
      };

      scribe.commands.insertOrderedList = new InsertListCommand('insertOrderedList');
      scribe.commands.insertUnorderedList = new InsertListCommand('insertUnorderedList');
    };
  };

});
