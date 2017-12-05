// Directives

jQuery.extend( jQuery.fn, {
    // Name of our method & one argument (the parent selector)
    within: function( pSelector ) {
        // Returns a subset of items using jQuery.filter
        return this.filter(function(){
            // Return truthy/falsey based on presence in parent
            return $(this).closest( pSelector ).length;
        });
    }
});

Vue.directive('elastic', require('./directives/elastic.js'));

Vue.directive('tip', require('./directives/tip.js'));

Vue.directive('focus', function (focusable) {
    if (! focusable) {
        return;
    }

    if ($('[autofocus]').length > 0 && ! $(this.el).within('.form-group').length) {
      return;
    }

    this.vm.$nextTick(() => this.el.focus());
})
