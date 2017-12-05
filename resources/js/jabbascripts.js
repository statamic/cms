require('./cp');

var $ = require('jquery');
var Mousetrap = require('mousetrap');

Vue.config.debug = false;
Vue.config.silent = true;

require('./plugins');
require('./filters');
require('./mixins');
require('./components');
require('./fieldtypes');
require('./directives');

Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#csrf-token').getAttribute('value');

Vue.http.interceptors.push({
    response: function (response) {
        if (response.status === 401) {
            window.location = response.data.redirect;
        }

        return response;
    }
});
