define([], function () {

  'use strict';

  return function () {
    return function (scribe) {
      // TODO: do we need to run this on every key press, or could we
      //       detect when the issue may have occurred?
      // TODO: run in a transaction so as to record the change? how do
      //       we know in advance whether there will be a change though?
      // TODO: share somehow with `InsertList` command

      var nodeHelpers = scribe.node;

      if (scribe.allowsBlockElements()) {
        scribe.el.addEventListener('keyup', function (event) {
          if (event.keyCode === 8 || event.keyCode === 46) { // backspace or delete

            var selection = new scribe.api.Selection();

            // Note: the range is always collapsed on keyup here
            var containerPElement = selection.getContaining(function (node) {
              return node.nodeName === 'P';
            });
            if (containerPElement) {
              /**
               * The 'input' event listener has already triggered
               * and recorded the faulty content as an item in the
               * UndoManager. We interfere with the undoManager
               * by force merging that transaction with the next
               * transaction which produce a clean one instead.
               *
               * FIXME: ideally we would not trigger a
               * 'content-changed' event with faulty HTML at all, but
               * it's too late to cancel it at this stage (and it's
               * not happened yet at keydown time).
               */

              scribe.transactionManager.run(function () {
                // Store the caret position
                selection.placeMarkers();
                nodeHelpers.removeChromeArtifacts(containerPElement);
                selection.selectMarkers();
              }, true);
            }
          }
        });
      }
    };
  };
});
