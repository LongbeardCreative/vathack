/**
 * nuSelectable - jQuery Plugin
 * Copyright (c) 2015, Alex Suyun
 * Copyrights licensed under The MIT License (MIT)
 */
;
(function($, window, document, undefined) {

    'use strict';

    var plugin = 'nuSelectable';

    var options = {};

    var nuSelectable = function(container, options) {
        this.container = $(container);
        this.offset = $(container).offset();
        this.options = $.extend({}, options, options);
        this.selection = $('<div>').addClass(this.options.selectionClass);
        this.items = $(this.options.items);
        this.init();
    };

    nuSelectable.prototype.init = function() {

        if (!this.options.autoRefresh) {
            this.itemData = this._cacheItemData();
        }
        this.selecting = false;
        this._normalizeContainer();
        this._bindEvents();
        return true;
    };


    nuSelectable.prototype._normalizeContainer = function() {
        this.container.css({
            '-webkit-touch-callout': 'none',
            '-webkit-user-select': 'none',
            '-khtml-user-select': 'none',
            '-moz-user-select': 'none',
            '-ms-user-select': 'none',
            'user-select': 'none'
        });
    };


    nuSelectable.prototype._cacheItemData = function() {
        var itemData = [],
            itemsLength;
        itemsLength = this.items.length;

        for (var i = 0, item; item = $(this.items[i]), i < itemsLength; i++) {
            itemData.push({
                element: item,
                selected: item.hasClass(this.options.selectedClass),
                selecting: false,
                position: item[0].getBoundingClientRect()
            });
        }
        return itemData;
    };

    nuSelectable.prototype._collisionDetector = function() {
        var selector = this.selection[0].getBoundingClientRect(),
            dataLength = this.itemData.length;

        // Using native for loop vs $.each for performance (no overhead)
        for (var i = dataLength - 1, item; item = this.itemData[i], i >= 0; i--) {

            var collided = !(selector.right < item.position.left ||
                selector.left > item.position.right ||
                selector.bottom < item.position.top ||
                selector.top > item.position.bottom);

            if (collided) {
                if (item.selected) {
                    item.element.removeClass(this.options.selectedClass);
                    item.selected = false;
                }
                if (!item.selected) {
                    item.element.addClass(this.options.selectedClass);
                    // select all children
                    item.element.nextAll().find(this.options.items).addClass(this.options.selectedClass);
                    item.selected = true;
                }
            } else {
                if (this.selecting) {
                    item.element.removeClass(this.options.selectedClass);
                }
            }
        }

        // get all selected nodes
        var selected = $('.'+this.options.selectedClass),
            commonAncestors = [];

        // find common ancestors for all node pairs
        for (var i = selected.length - 1, itemA; itemA = selected[i], i >= 0; i--) {

            for (var j = selected.length - 1, itemB; itemB = selected[j], j >= 0; j--) {

                if ( i != j ) {

                    var ancestor = $(itemA).parents().has(itemB).first();

                    if (commonAncestors.indexOf(ancestor[0]) == -1) {
                        commonAncestors.push(ancestor[0]);
                    }
                }
            }
        }

        // highlight all nodes between top ancestors
        for (var i = commonAncestors.length - 1, ancestor; ancestor = commonAncestors[i], i >= 0; i--) {
            
            // highlight
            $(ancestor)
                .children('.ct-dom-tree-node')
                .has('.'+this.options.selectedClass)
                .find(this.options.items)
                .addClass(this.options.selectedClass);

            // look for nodes beetween highlighted
            var nodes = $(ancestor).children('.ct-dom-tree-node'),
                first = false,
                last = false;

            // very last ancestor
            for (var j = nodes.length - 1, node; node = nodes[j], j >= 0; j--) {
                if ( $(node).has('.'+this.options.selectedClass).length > 0 && last === false ) {
                    last = j;
                }

            }

            // very first ancestor
            for (var j = 0, node; node = nodes[j], j <= nodes.length - 1; j++) {
                if ( $(node).has('.'+this.options.selectedClass).length > 0 && first === false ) {
                    first = j;
                }
            }

            // if something between
            if ( last - first > 1 ) {
                // highlight all nodes beetween
                for (var j = first, node; node = nodes[j], j <= last; j++) {
                    $(node)
                        .find(this.options.items)
                        .addClass(this.options.selectedClass);
                }
            }
        }

        // custom callback
        this.options.onMove(selected);

        //console.log(commonAncestors);
    };


    nuSelectable.prototype._createSelection = function(x, y) {
        this.selection.css({
            'position': 'absolute',
            'top': y + 'px',
            'left': x + 'px',
            'width': '0',
            'height': '0',
            'z-index': '999',
            'overflow': 'hidden'
        }).appendTo(this.container);
    };


    nuSelectable.prototype._drawSelection = function(width, height, x, y) {
        this.selection.css({
            'width': width,
            'height': height,
            'top': y,
            'left': x
        });
    };

    nuSelectable.prototype.clear = function() {
        this.items.removeClass(this.options.selectedClass);
    };

    nuSelectable.prototype._mouseDown = function(event) {
        event.preventDefault();
        event.stopPropagation();

        this.items = $(this.options.items);

        if (this.options.disable) {
            return false;
        }
        if (event.which !== 1) {
            return false;
        }
        if (this.options.autoRefresh) {
            this.itemData = this._cacheItemData();
        }
        if (event.metaKey || event.ctrlKey) {
            this.selecting = false;
        } else {
            this.selecting = true;
        }
        this.pos = [event.pageX - this.offset.left, event.pageY - this.offset.top + $(this.container).scrollTop()];
        this._createSelection(event.pageX - this.offset.left, event.pageY - this.offset.top + $(this.container).scrollTop());

        // custom callback
        this.options.onMouseDown();
    };

    nuSelectable.prototype._mouseMove = function(event) {
        event.preventDefault();
        event.stopPropagation();
        // Save some bytes
        var pos = this.pos;
        if (!pos) {
            return false;
        }
        var newpos = [event.pageX - this.offset.left, event.pageY - this.offset.top + $(this.container).scrollTop()],
            width = Math.abs(newpos[0] - pos[0]),
            height = Math.abs(newpos[1] - pos[1]),
            top, left;

        top = (newpos[0] < pos[0]) ? (pos[0] - width) : pos[0];
        left = (newpos[1] < pos[1]) ? (pos[1] - height) : pos[1];
        this._drawSelection(width, height, top, left);
        this._collisionDetector();

    };


    nuSelectable.prototype._mouseUp = function(event) {
        event.preventDefault();
        event.stopPropagation();
        if (!this.pos) {
            return false;
        }
        this.selecting = false;
        this.selection.remove();
        if (event.pageX-this.offset.left === this.pos[0] && event.pageY-this.offset.top + $(this.container).scrollTop() === this.pos[1]) {
            this.clear();
        }
    };

    nuSelectable.prototype._bindEvents = function() {
        this.container.on('mousedown', $.proxy(this._mouseDown, this));
        this.container.on('mousemove', $.proxy(this._mouseMove, this));
        this.container.on('mouseup', $.proxy(this._mouseUp, this));
    };

    $.fn[plugin] = function(options) {
        var args = Array.prototype.slice.call(arguments, 1);

        return this.each(function() {
            var item = $(this),
                instance = item.data(plugin);
            if (!instance) {
                item.data(plugin, new nuSelectable(this, options));
            } else {

                if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {
                    instance[options].apply(instance, args);
                }
            }

        });
    };

})(jQuery, window, document);
