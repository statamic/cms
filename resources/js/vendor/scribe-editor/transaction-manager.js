define([
  './events'
  ], function (events) {

  'use strict';

  return function (scribe) {
    function TransactionManager() {
      this.history = [];
    }

    Object.assign(TransactionManager.prototype, {
      start: function () {
        this.history.push(1);
      },

      end: function () {
        this.history.pop();

        if (this.history.length === 0) {
          scribe.pushHistory();
          scribe.trigger(events.legacyContentChanged);
          scribe.trigger(events.contentChanged);
        }
      },

      run: function (transaction, forceMerge) {
        this.start();
        // If there is an error, don't prevent the transaction from ending.
        try {
          if (transaction) {
            transaction();
          }
        } finally {
          scribe._forceMerge = forceMerge === true;
          this.end();
          scribe._forceMerge = false;
        }
      }
    });

    return TransactionManager;
  };
});
