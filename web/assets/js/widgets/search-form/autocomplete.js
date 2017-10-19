/**
 * Drupal 7 native autocomplete implementation.
 */

(function ($) {

    /**
     * Attaches the autocomplete behavior to all required fields.
     */
    CultuurnetWidgets.behaviors.autocomplete = {
        attach: function (context, settings) {
            var acdb = [];
            $('input.cnw_form-autocomplete', context).each(function() {
                var $input = $(this)
                    .attr('autocomplete', 'OFF')
                    .attr('aria-autocomplete', 'list');

                var uri = $input.data('autocomplete-path');
                if (!acdb[uri]) {
                    acdb[uri] = new CultuurnetWidgets.ACDB(uri);
                }

                $input.parent()
                    .attr('role', 'application')
                    .append($('<span class="element-invisible" aria-live="assertive"></span>')
                        .attr('id', $input.attr('id') + '-autocomplete-aria-live')
                    );
                new CultuurnetWidgets.jsAC($input, acdb[uri]);
            });

        
        }
    };

    /**
     * Prevents the form from submitting if the suggestions popup is open
     * and closes the suggestions popup when doing so.
     */
    CultuurnetWidgets.autocompleteSubmit = function () {
        return $('#autocomplete').each(function () {
                this.owner.hidePopup();
            }).length == 0;
    };

    /**
     * An AutoComplete object.
     */
    CultuurnetWidgets.jsAC = function ($input, db) {
        var ac = this;
        this.input = $input[0];
        this.ariaLive = $('#' + this.input.id + '-autocomplete-aria-live');
        this.db = db;

        $input
            .keydown(function (event) { return ac.onkeydown(this, event); })
            .keyup(function (event) { ac.onkeyup(this, event); })
            .blur(function () { ac.hidePopup(); ac.db.cancel(); });

    };

    /**
     * Handler for the "keydown" event.
     */
    CultuurnetWidgets.jsAC.prototype.onkeydown = function (input, e) {
        if (!e) {
            e = window.event;
        }
        switch (e.keyCode) {
            case 40: // down arrow.
                this.selectDown();
                return false;
            case 38: // up arrow.
                this.selectUp();
                return false;
            default: // All other keys.
                return true;
        }
    };

    /**
     * Handler for the "keyup" event.
     */
    CultuurnetWidgets.jsAC.prototype.onkeyup = function (input, e) {
        if (!e) {
            e = window.event;
        }
        switch (e.keyCode) {
            case 16: // Shift.
            case 17: // Ctrl.
            case 18: // Alt.
            case 20: // Caps lock.
            case 33: // Page up.
            case 34: // Page down.
            case 35: // End.
            case 36: // Home.
            case 37: // Left arrow.
            case 38: // Up arrow.
            case 39: // Right arrow.
            case 40: // Down arrow.
                return true;

            case 9:  // Tab.
            case 13: // Enter.
            case 27: // Esc.
                this.hidePopup(e.keyCode);
                return true;

            default: // All other keys.
                if (input.value.length > 0 && !input.readOnly) {
                    this.populatePopup();
                }
                else {
                    this.hidePopup(e.keyCode);
                }
                return true;
        }
    };

    /**
     * Puts the currently highlighted suggestion into the autocomplete field.
     */
    CultuurnetWidgets.jsAC.prototype.select = function (node) {
        console.log($(node));
        this.input.value = $(node)[0].innerText;
    };

    /**
     * Highlights the next suggestion.
     */
    CultuurnetWidgets.jsAC.prototype.selectDown = function () {
        if (this.selected && this.selected.nextSibling) {
            this.highlight(this.selected.nextSibling);
        }
        else if (this.popup) {
            var lis = $('li', this.popup);
            if (lis.length > 0) {
                this.highlight(lis.get(0));
            }
        }
    };

    /**
     * Highlights the previous suggestion.
     */
    CultuurnetWidgets.jsAC.prototype.selectUp = function () {
        if (this.selected && this.selected.previousSibling) {
            this.highlight(this.selected.previousSibling);
        }
    };

    /**
     * Highlights a suggestion.
     */
    CultuurnetWidgets.jsAC.prototype.highlight = function (node) {
        if (this.selected) {
            $(this.selected).removeClass('autocomplete-option-selected');
        }
        $(node).addClass('autocomplete-option-selected');
        this.selected = node;
        $(this.ariaLive).html($(this.selected).html());
    };

    /**
     * Unhighlights a suggestion.
     */
    CultuurnetWidgets.jsAC.prototype.unhighlight = function (node) {
        $(node).removeClass('autocomplete-option-selected');
        this.selected = false;
        $(this.ariaLive).empty();
    };

    /**
     * Hides the autocomplete suggestions.
     */
    CultuurnetWidgets.jsAC.prototype.hidePopup = function (keycode) {

        // Select item if the right key or mousebutton was pressed.
        if (this.selected && ((keycode && keycode != 46 && keycode != 8 && keycode != 27) || !keycode)) {
            this.input.value = $(this.selected).data('autocompleteValue');
        }
        // Hide popup.
        var popup = this.popup;
        if (popup) {
            this.popup = null;
            $(popup).fadeOut('fast', function () { $(popup).remove(); });
        }
        this.selected = false;
        $(this.ariaLive).empty();
    };

    /**
     * Positions the suggestions popup and starts a search.
     */
    CultuurnetWidgets.jsAC.prototype.populatePopup = function () {
        var $input = $(this.input);
        var position = $input.position();
        // Show popup.
        if (this.popup) {
            $(this.popup).remove();
        }
        this.selected = false;
        this.popup = $('<div id="autocomplete"></div>')[0];
        this.popup.owner = this;
        $(this.popup).css({

            width: $input.outerWidth() + 'px',
            display: 'none'
        });
        $input.after(this.popup);

        // Do search.
        this.db.owner = this;
        this.db.search(this.input.value);
    };

    /**
     * Fills the suggestion popup with any matches received.
     */
    CultuurnetWidgets.jsAC.prototype.found = function (matches) {
        // If no value in the textfield, do not show the popup.
        if (!this.input.value.length) {
            return false;
        }

        // Prepare matches.
        var ul = $('<ul></ul>');
        var ac = this;
        for (key in matches) {
            $('<li></li>')
                .html($('<div></div>').html(matches[key]))
                .mousedown(function () { ac.select(this); })
                .mouseover(function () { ac.highlight(this); })
                .mouseout(function () { ac.unhighlight(this); })
                .data('autocompleteValue', key)
                .appendTo(ul);
        }

        // Show popup with matches, if any.
        if (this.popup) {
            if (ul.children().length) {
                $(this.popup).empty().append(ul).show();
                $(this.ariaLive).html('Autocomplete popup');
                $(this.input).addClass('cnw_form-autocomplete--hasresults');
            }
            else {
                $(this.popup).css({ visibility: 'hidden' });
                this.hidePopup();
                $(this.input).removeClass('cnw_form-autocomplete--hasresults');
            }
        }
    };

    CultuurnetWidgets.jsAC.prototype.setStatus = function (status) {
        switch (status) {
            case 'begin':
                $(this.input).addClass('throbbing');
                $(this.ariaLive).html('Bezig met zoeken');
                break;
            case 'cancel':
            case 'error':
            case 'found':
                $(this.input).removeClass('throbbing');
                break;
        }
    };

    /**
     * An AutoComplete DataBase object.
     */
    CultuurnetWidgets.ACDB = function (uri) {
        this.uri = uri;
        this.delay = 300;
        this.cache = {};
    };

    /**
     * Performs a cached and delayed search.
     */
    CultuurnetWidgets.ACDB.prototype.search = function (searchString) {
        var db = this;
        this.searchString = searchString;

        // See if this string needs to be searched for anyway.
        searchString = searchString.replace(/^\s+|\s+$/, '');
        if (searchString.length <= 0 ||
            searchString.charAt(searchString.length - 1) == ',') {
            return;
        }

        // See if this key has been searched for before.
        if (this.cache[searchString]) {
            return this.owner.found(this.cache[searchString]);
        }

        // Initiate delayed search.
        if (this.timer) {
            clearTimeout(this.timer);
        }
        this.timer = setTimeout(function () {
            db.owner.setStatus('begin');

            // Ajax GET request for autocompletion. We use CultuurnetWidgets.encodePath instead of
            // encodeURIComponent to allow autocomplete search terms to contain slashes.
            $.ajax({
                type: 'GET',
                url: db.uri + '/' + encodeURIComponent(searchString).replace(/%2F/g, '/'),
                dataType: 'json',
                success: function (matches) {
                    if (typeof matches.status == 'undefined' || matches.status != 0) {
                        db.cache[searchString] = matches;
                        // Verify if these are still the matches the user wants to see.
                        if (db.searchString == searchString) {
                            db.owner.found(matches);
                        }
                        db.owner.setStatus('found');
                    }
                }
            });
        }, this.delay);
    };

    /**
     * Cancels the current autocomplete request.
     */
    CultuurnetWidgets.ACDB.prototype.cancel = function () {
        if (this.owner) this.owner.setStatus('cancel');
        if (this.timer) clearTimeout(this.timer);
        this.searchString = '';
    };

})(jQuery);
