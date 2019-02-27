define([], function() {

  function determineMutationObserver(window) {
    // This enables server side rendering
    if (typeof window === 'undefined') {
      // Stub observe function to avoid error
      return function() {
        return {
          observe: function() {}
        };
      }
    } else {
      return window.MutationObserver ||
        window.WebKitMutationObserver ||
        window.MozMutationObserver;
    }
  }

  return {
    determineMutationObserver: determineMutationObserver
  };
});
