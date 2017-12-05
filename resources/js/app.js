import Notifications from './mixins/Notifications.js';
require('./components/NotificationBus');

var vm = new Vue({
    el: '#statamic',

    mixins: [ Notifications ],

    data: {
        isPublishPage: false,
        isPreviewing: false,
        showShortcuts: false,
        navVisible: false,
        version: Statamic.version,
        draggingNonFile: false,
        sneakPeekViewport: null,
        sneakPeekFields: null,
    },

    computed: {
        hasSearchResults: function() {
            return this.$refs.search.hasItems;
        }
    },

    methods: {
        preview: function() {
            var self = this;
            self.$broadcast('previewing');
            self.isPreviewing = true;

            this.sneakPeekViewport = $('.sneak-peek-viewport')[0];
            this.sneakPeekFields = $('.page-wrapper')[0];

            $('.sneak-peek-wrapper').addClass('animating on');

            this.wait(200).then(() => {
                let width = localStorage.getItem('statamic.sneakpeek.width') || 400;
                this.sneakPeekViewport.style.left = width + 'px';
                this.sneakPeekFields.style.width = width + 'px';
                $(this.$el).addClass('sneak-peeking');
                return this.wait(200);
            }).then(() => {
                $('#sneak-peek-iframe').show();
                $(this.$el).addClass('sneak-peek-editing sneak-peek-animating');
                return this.wait(500);
            }).then(() => {
                $(this.$el).removeClass('sneak-peek-animating');
            });
        },

        stopPreviewing: function() {
            this.isPreviewing = false;
            this.$broadcast('previewing.stopped');

            $('.sneak-peek-wrapper').addClass('animating');
            $(this.$el).addClass('sneak-peek-animating');
            $(this.$el).removeClass('sneak-peek-editing');
            $('#sneak-peek-iframe').fadeOut();
            $('.sneak-peek-wrapper .icon').hide();

            this.wait(500).then(() => {
                this.sneakPeekViewport.style.left = null;
                this.sneakPeekFields.style.width = null;
                $(this.$el).removeClass('sneak-peek-animating');
                $(this.$el).removeClass('sneak-peeking');
                return this.wait(200);
            }).then(() => {
                $('.sneak-peek-wrapper').removeClass('on');
                return this.wait(200);
            }).then(() => {
                $('.sneak-peek-wrapper').removeClass('animating');
            });
        },

        /**
         * Returns a promise after specified milliseconds
         *
         * A nice alternative to nested setTimeouts.
         */
        wait(ms) {
            return new Promise(resolve => {
                setTimeout(resolve, ms);
            });
        },

        toggleNav: function () {
            this.navVisible = !this.navVisible;
        },

        /**
         * When the dragstart event is triggered.
         *
         * This event doesn't get triggered when dragging something from outside the browser,
         * so we can determine that something other than a file is being dragged.
         */
        dragStart() {
            this.draggingNonFile = true;
        },

        /**
         * When the dragend event is triggered.
         *
         * This event doesn't get triggered when dragging something from outside the browser,
         * so we can determine that something other than a file is being dragged.
         */
        dragEnd() {
            this.draggingNonFile = false;
        },

        sneakPeekResizeStart(e) {
            window.addEventListener('mousemove', this.sneakPeekResizing);
            window.addEventListener('mouseup', this.sneakPeekResizeEnd);
            $('.sneak-peek-iframe-wrap').css('pointer-events', 'none');
        },

        sneakPeekResizeEnd(e) {
            window.removeEventListener('mousemove', this.sneakPeekResizing, false);
            window.removeEventListener('mouseup', this.sneakPeekResizeEnd, false);
            $('.sneak-peek-iframe-wrap').css('pointer-events', 'auto');
        },

        sneakPeekResizing(e) {
            e.preventDefault();

            let width = e.clientX;

            // Prevent the width being too narrow.
            width = (width < 350) ? 350 : width;

            this.sneakPeekViewport.style.left = width + 'px';
            this.sneakPeekFields.style.width = width + 'px';

            localStorage.setItem('statamic.sneakpeek.width', width);
        },

        stickyHeader() {
            const header = $('.sticky').first();

            if (! header.length) return;

            document.addEventListener('scroll', (e) => {
                const win = $(window);
                header.parent().toggleClass('stuck', win.scrollTop() > 90);
            });
        },
    },

    ready: function() {
        Mousetrap.bind(['/', 'ctrl+f'], function(e) {
            $('#global-search').focus();
        }, 'keyup');

        Mousetrap.bind('?', function(e) {
            this.showShortcuts = true;
        }.bind(this), 'keyup');

        Mousetrap.bind('escape', function(e) {
            this.$broadcast('close-modal');
            this.$broadcast('close-editor');
            this.$broadcast('close-selector');
            this.$broadcast('close-dropdown', null);
        }.bind(this), 'keyup');

        // Keep track of whether something other than a file is being dragged
        // so that components can tell when a file is being dragged.
        window.addEventListener('dragstart', this.dragStart);
        window.addEventListener('dragend', this.dragEnd);

        this.stickyHeader();
    },

    events: {
        'changesMade': function (changed) {
            // If true, a confirmation dialog will be displayed when the user tries to
            // navigate away (or refresh, etc). If false, the dialog will no longer show.
            if (changed) {
                window.onbeforeunload = () => '';
            } else {
                window.onbeforeunload = null;
            }
        }
    }
});
