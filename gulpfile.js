var elixir = require('laravel-elixir');
var gulp = require('gulp');

require('laravel-elixir-vueify');
require('laravel-elixir-svg-symbols');

// Vendor paths
var paths = {
    'sweetalert': './node_modules/sweetalert/dist/'
};

elixir(function(mix) {

    /**
     * This is the tale / Of Captain Jack Sparrow
     * Pirate so brave / On the Seven Seas
     * A mystical quest / To the Isle of Tortuga
     * Raven locks sway / On the ocean's breeze
     */
    mix.scripts([
        "./node_modules/jquery/dist/jquery.js",
        "./node_modules/bootstrap/dist/js/bootstrap.js",
        "./node_modules/underscore/underscore.js",
        "./node_modules/vue/dist/vue.js",
        "./node_modules/vue-resource/dist/vue-resource.js",
        "./node_modules/moment/moment.js",
        "./node_modules/jquery-serializejson/jquery.serializejson.js",
        "./node_modules/autosize/dist/autosize.js",
        "./node_modules/trix/dist/trix.js",
        "./node_modules/slug/slug.js",
        "./node_modules/tippy.js/dist/tippy.min.js",
        "./node_modules/luminous-lightbox/dist/luminous.js",
        "resources/js/vendor/dmuploader.js",
        "resources/js/vendor/calendar.js",
        "resources/js/vendor/redactor.js",
        "resources/js/vendor/redactor/assets.js",
        "resources/js/vendor/selectize.js",
        "resources/js/vendor/jquery-ui.js",
        "resources/js/vendor/nested-sortable.js",
    ], 'resources/dist/js/johnny-deps.js');

    mix.browserify('cp.js', 'resources/dist/js/cp.js');
    mix.browserify('jabbascripts.js', 'resources/dist/js/jabbascripts.js');
    mix.browserify('app.js', 'resources/dist/js/app.js');

    // Build css file
    mix.sass('cp.scss', 'resources/dist/css/cp.css', {
        includePaths: [
            paths.sweetalert
        ]
    });

    mix.svgSprite('resources/svg/', 'resources/dist/svg/');

    // Copy assets into a location ready for distribution
    mix.copy('resources/img/**/*.*', 'resources/dist/img/');
    mix.copy('resources/svg/**/*.*', 'resources/dist/svg/');
    mix.copy('resources/audio/**/*.*', 'resources/dist/audio/');
    mix.copy('resources/fonts/**/*.*', 'resources/dist/fonts/');
});
