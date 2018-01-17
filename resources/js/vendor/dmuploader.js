/*
 * dmuploader.js - Jquery File Uploader - 0.1
 * http://www.daniel.com.uy/projects/jquery-file-uploader/
 *
 * Copyright (c) 2013 Daniel Morales
 * Dual licensed under the MIT and GPL licenses.
 * http://www.daniel.com.uy/doc/license/
 */

(function($) {
    var pluginName = 'dmUploader';

    // These are the plugin defaults values
    var defaults = {
        url: document.URL,
        method: 'POST',
        extraData: {},
        maxFileSize: 0,
        maxFiles: 0,
        allowedTypes: '*',
        extFilter: null,
        dataType: null,
        fileName: 'file',
        onInit: function(){},
        onFallbackMode: function() {message},
        onNewFile: function(id, file){},
        onBeforeUpload: function(id){},
        onComplete: function(){},
        onUploadProgress: function(id, percent){},
        onUploadSuccess: function(id, data){},
        onUploadError: function(id, message){},
        onFileTypeError: function(file){},
        onFileSizeError: function(file){},
        onFileExtError: function(file){},
        onFilesMaxError: function(file){}
    };

    var DmUploader = function(element, options)
    {
        this.element = $(element);

        this.settings = $.extend({}, defaults, options);

        if(!this.checkBrowser()){
            return false;
        }

        this.init();

        return true;
    };

    DmUploader.prototype.checkBrowser = function()
    {
        if(window.FormData === undefined){
            this.settings.onFallbackMode.call(this.element, 'Browser doesn\'t support Form API');

            return false;
        }

        if(this.element.find('input[type=file]').length > 0){
            return true;
        }

        if (!this.checkEvent('drop', this.element) || !this.checkEvent('dragstart', this.element)){
            this.settings.onFallbackMode.call(this.element, 'Browser doesn\'t support Ajax Drag and Drop');

            return false;
        }

        return true;
    };

    DmUploader.prototype.checkEvent = function(eventName, element)
    {
        var element = element || document.createElement('div');
        var eventName = 'on' + eventName;

        var isSupported = eventName in element;

        if(!isSupported){
            if(!element.setAttribute){
                element = document.createElement('div');
            }
            if(element.setAttribute && element.removeAttribute){
                element.setAttribute(eventName, '');
                isSupported = typeof element[eventName] == 'function';

                if(typeof element[eventName] != 'undefined'){
                    element[eventName] = undefined;
                }
                element.removeAttribute(eventName);
            }
        }

        element = null;
        return isSupported;
    };

    DmUploader.prototype.init = function()
    {
        var widget = this;

        widget.queue = new Array();
        widget.queuePos = -1;
        widget.queueRunning = false;

        // -- Drag and drop event
        widget.element.on('drop', function (evt){
            evt.preventDefault();
            var files = evt.originalEvent.dataTransfer.files;

            widget.queueFiles(files);
        });

        //-- Optional File input to make a clickable area
        widget.element.find('input[type=file]').on('change', function(evt){
            var files = evt.target.files;

            widget.queueFiles(files);

            $(this).val('');
        });

        this.settings.onInit.call(this.element, this);
    };

    DmUploader.prototype.queueFiles = function(files)
    {
        var j = this.queue.length;

        for (var i= 0; i < files.length; i++)
        {
            var file = files[i];

            // Check file size
            if((this.settings.maxFileSize > 0) &&
                (file.size > this.settings.maxFileSize)){

                this.settings.onFileSizeError.call(this.element, file);

                continue;
            }

            // Check file type
            if((this.settings.allowedTypes != '*') &&
                !file.type.match(this.settings.allowedTypes)){

                this.settings.onFileTypeError.call(this.element, file);

                continue;
            }

            // Check file extension
            if(this.settings.extFilter != null){
                var extList = this.settings.extFilter.toLowerCase().split(';');

                var ext = file.name.toLowerCase().split('.').pop();

                if($.inArray(ext, extList) < 0){
                    this.settings.onFileExtError.call(this.element, file);

                    continue;
                }
            }

            // Check max files
            if(this.settings.maxFiles > 0) {
                if(this.queue.length >= this.settings.maxFiles) {
                    this.settings.onFilesMaxError.call(this.element, file);

                    continue;
                }
            }

            this.queue.push(file);

            var index = this.queue.length - 1;

            this.settings.onNewFile.call(this.element, index, file);
        }

        // Only start Queue if we haven't!
        if(this.queueRunning){
            return false;
        }

        // and only if new Failes were successfully added
        if(this.queue.length == j){
            return false;
        }

        this.processQueue();

        return true;
    };

    DmUploader.prototype.processQueue = function()
    {
        var widget = this;

        widget.queuePos++;

        if(widget.queuePos >= widget.queue.length){
            // Cleanup

            widget.settings.onComplete.call(widget.element);

            // Wait until new files are droped
            widget.queuePos = (widget.queue.length - 1);

            widget.queueRunning = false;

            return;
        }

        var file = widget.queue[widget.queuePos];

        // Form Data
        var fd = new FormData();
        fd.append(widget.settings.fileName, file);

        // Return from client function (default === undefined)
        var can_continue = widget.settings.onBeforeUpload.call(widget.element, widget.queuePos);

        // If the client function doesn't return FALSE then continue
        if( false === can_continue ) {
            return;
        }

        // Append extra Form Data
        $.each(widget.settings.extraData, function(exKey, exVal){
            fd.append(exKey, exVal);
        });

        widget.queueRunning = true;

        // Ajax Submit
        $.ajax({
            url: widget.settings.url,
            type: widget.settings.method,
            dataType: widget.settings.dataType,
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            forceSync: false,
            xhr: function(){
                var xhrobj = $.ajaxSettings.xhr();
                if(xhrobj.upload){
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total || e.totalSize;
                        if(event.lengthComputable){
                            percent = Math.ceil(position / total * 100);
                        }

                        widget.settings.onUploadProgress.call(widget.element, widget.queuePos, percent);
                    }, false);
                }

                return xhrobj;
            },
            success: function (data, message, xhr){
                widget.settings.onUploadSuccess.call(widget.element, widget.queuePos, data);
            },
            error: function (xhr, status, errMsg){
                widget.settings.onUploadError.call(widget.element, widget.queuePos, errMsg, xhr);
            },
            complete: function(xhr, textStatus){
                widget.processQueue();
            }
        });
    }

    $.fn.dmUploader = function(options){
        return this.each(function(){
            if(!$.data(this, pluginName)){
                $.data(this, pluginName, new DmUploader(this, options));
            }
        });
    };

    // -- Disable Document D&D events to prevent opening the file on browser when we drop them
    $(document).on('dragenter', function (e) { e.stopPropagation(); e.preventDefault(); });
    $(document).on('dragover', function (e) { e.stopPropagation(); e.preventDefault(); });
    $(document).on('drop', function (e) { e.stopPropagation(); e.preventDefault(); });
})(jQuery);
