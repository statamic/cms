(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var vm = new Vue({
    el: '#statamic',

    data: {
        isPublishPage: false,
        isPreviewing: false,
        showShortcuts: false,
        navVisible: false,
        version: Statamic.version,
        flashSuccess: Statamic.flashSuccess,
        flashError: false,
        flashSuccessTimer: null,
        draggingNonFile: false,
        sneakPeekViewport: null,
        sneakPeekFields: null
    },

    computed: {
        hasSearchResults: function hasSearchResults() {
            return this.$refs.search.hasItems;
        }
    },

    methods: {
        preview: function preview() {
            var _this = this;

            var self = this;
            self.$broadcast('previewing');
            self.isPreviewing = true;

            this.sneakPeekViewport = $('.sneak-peek-viewport')[0];
            this.sneakPeekFields = $('.page-wrapper')[0];

            $('.sneak-peek-wrapper').addClass('animating on');

            this.wait(200).then(function () {
                var width = localStorage.getItem('statamic.sneakpeek.width') || 400;
                _this.sneakPeekViewport.style.left = width + 'px';
                _this.sneakPeekFields.style.width = width + 'px';
                $(_this.$el).addClass('sneak-peeking');
                return _this.wait(200);
            }).then(function () {
                $('#sneak-peek-iframe').show();
                $(_this.$el).addClass('sneak-peek-editing sneak-peek-animating');
                return _this.wait(500);
            }).then(function () {
                $(_this.$el).removeClass('sneak-peek-animating');
            });
        },

        stopPreviewing: function stopPreviewing() {
            var _this2 = this;

            this.isPreviewing = false;
            this.$broadcast('previewing.stopped');

            $('.sneak-peek-wrapper').addClass('animating');
            $(this.$el).addClass('sneak-peek-animating');
            $(this.$el).removeClass('sneak-peek-editing');
            $('#sneak-peek-iframe').fadeOut();
            $('.sneak-peek-wrapper .icon').hide();

            this.wait(500).then(function () {
                _this2.sneakPeekViewport.style.left = null;
                _this2.sneakPeekFields.style.width = null;
                $(_this2.$el).removeClass('sneak-peek-animating');
                $(_this2.$el).removeClass('sneak-peeking');
                return _this2.wait(200);
            }).then(function () {
                $('.sneak-peek-wrapper').removeClass('on');
                return _this2.wait(200);
            }).then(function () {
                $('.sneak-peek-wrapper').removeClass('animating');
            });
        },

        /**
         * Returns a promise after specified milliseconds
         *
         * A nice alternative to nested setTimeouts.
         */
        wait: function wait(ms) {
            return new Promise(function (resolve) {
                setTimeout(resolve, ms);
            });
        },


        toggleNav: function toggleNav() {
            this.navVisible = !this.navVisible;
        },

        /**
         * When the dragstart event is triggered.
         *
         * This event doesn't get triggered when dragging something from outside the browser,
         * so we can determine that something other than a file is being dragged.
         */
        dragStart: function dragStart() {
            this.draggingNonFile = true;
        },


        /**
         * When the dragend event is triggered.
         *
         * This event doesn't get triggered when dragging something from outside the browser,
         * so we can determine that something other than a file is being dragged.
         */
        dragEnd: function dragEnd() {
            this.draggingNonFile = false;
        },
        sneakPeekResizeStart: function sneakPeekResizeStart(e) {
            window.addEventListener('mousemove', this.sneakPeekResizing);
            window.addEventListener('mouseup', this.sneakPeekResizeEnd);
            $('.sneak-peek-iframe-wrap').css('pointer-events', 'none');
        },
        sneakPeekResizeEnd: function sneakPeekResizeEnd(e) {
            window.removeEventListener('mousemove', this.sneakPeekResizing, false);
            window.removeEventListener('mouseup', this.sneakPeekResizeEnd, false);
            $('.sneak-peek-iframe-wrap').css('pointer-events', 'auto');
        },
        sneakPeekResizing: function sneakPeekResizing(e) {
            e.preventDefault();

            var width = e.clientX;

            // Prevent the width being too narrow.
            width = width < 350 ? 350 : width;

            this.sneakPeekViewport.style.left = width + 'px';
            this.sneakPeekFields.style.width = width + 'px';

            localStorage.setItem('statamic.sneakpeek.width', width);
        },
        stickyHeader: function stickyHeader() {
            var header = $('.sticky').first();

            if (!header.length) return;

            document.addEventListener('scroll', function (e) {
                var win = $(window);
                header.parent().toggleClass('stuck', win.scrollTop() > 90);
            });
        }
    },

    ready: function ready() {
        var _this3 = this;

        Mousetrap.bind(['/', 'ctrl+f'], function (e) {
            $('#global-search').focus();
        }, 'keyup');

        Mousetrap.bind('?', function (e) {
            this.showShortcuts = true;
        }.bind(this), 'keyup');

        Mousetrap.bind('escape', function (e) {
            this.$broadcast('close-modal');
            this.$broadcast('close-editor');
            this.$broadcast('close-selector');
            this.$broadcast('close-dropdown', null);
        }.bind(this), 'keyup');

        // Clear the initial flash message after a second.
        this.flashSuccessTimer = setTimeout(function () {
            _this3.flashSuccess = null;
        }, 1000);

        // Keep track of whether something other than a file is being dragged
        // so that components can tell when a file is being dragged.
        window.addEventListener('dragstart', this.dragStart);
        window.addEventListener('dragend', this.dragEnd);

        this.stickyHeader();
    },

    events: {
        'setFlashSuccess': function setFlashSuccess(msg, timeout) {
            var _this4 = this;

            this.flashSuccess = msg;

            clearTimeout(this.flashSuccessTimer);

            if (timeout) {
                this.flashSuccessTimer = setTimeout(function () {
                    _this4.flashSuccess = null;
                }, timeout);
            }
        },
        'setFlashError': function setFlashError(msg) {
            this.flashError = msg;
        },
        'changesMade': function changesMade(changed) {
            // If true, a confirmation dialog will be displayed when the user tries to
            // navigate away (or refresh, etc). If false, the dialog will no longer show.
            if (changed) {
                window.onbeforeunload = function () {
                    return '';
                };
            } else {
                window.onbeforeunload = null;
            }
        }
    }
});

},{}]},{},[1]);

//# sourceMappingURL=app.js.map
