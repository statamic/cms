_.mixin({
    // ### _.objMap
    // _.map for objects, keeps key/value associations
    objMap: function (input, mapper, context) {
      return _.reduce(input, function (obj, v, k) {
               obj[k] = mapper.call(context, v, k, input);
               return obj;
             }, {}, context);
    },
    // ### _.objFilter
    // _.filter for objects, keeps key/value associations
    // but only includes the properties that pass test().
    objFilter: function (input, test, context) {
      return _.reduce(input, function (obj, v, k) {
               if (test.call(context, v, k, input)) {
                 obj[k] = v;
               }
               return obj;
             }, {}, context);
    },
    // ### _.objReject
    //
    // _.reject for objects, keeps key/value associations
    // but does not include the properties that pass test().
    objReject: function (input, test, context) {
      return _.reduce(input, function (obj, v, k) {
               if (!test.call(context, v, k, input)) {
                 obj[k] = v;
               }
               return obj;
             }, {}, context);
    }
});
