
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets, $) {

    'use strict';

    /**
     * Provide a behavior for facets.
     */
    CultuurnetWidgets.behaviors.facets = {

        attach: function(context) {
            // Click event binding for facet filters.
            $(context).find('a[data-facet-type]').each(function() {
                $(this).bind('click', function(e) {

                    e.preventDefault();

                    var $this = $(this);

                    // Retrieve all data values.
                    var widget_id = $this.parents('[data-widget-id]').data('widget-id');
                    var type = $this.data('facet-type');
                    var value = $this.data('facet-value');
                    var facet_id = $this.data('facet-id');
                    var option_id = $this.data('facet-option-id');

                    if ($this.parents('li').hasClass('active') === true) {
                        if (type !== 'custom') {
                            CultuurnetWidgets.removeFilter(widget_id, type, null);
                        }
                        else {
                            CultuurnetWidgets.removeFilter(widget_id, facet_id, option_id);
                        }
                    }
                    else {
                        if (type !== 'custom') {
                            CultuurnetWidgets.facetFilter(widget_id, type, value);
                        }
                        else {
                            CultuurnetWidgets.customFilter(widget_id, facet_id, option_id);
                        }
                    }
                });
            });
        }
    };

    /**
     * Facet filter function.
     *
     * @param widget_id
     * @param value
     * @param param
     */
    CultuurnetWidgets.facetFilter = function(widget_id, param, value) {
        // Change param to proper format.
        param = 'facets[' + widget_id + '][' + param + ']';

        // Check for existing query parameters.
        var queryString = window.location.search;
        if (queryString) {
            // Convert existing query string to an object.
            var currentParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');

            if (typeof currentParams[param] !== 'undefined' && value === 0) {
                delete currentParams[param];
            }
            else {
                currentParams[param] = value;
            }

            // Build a query string from updated params.
            var newParams = CultuurnetWidgets.buildQueryUrl(currentParams);

            window.location.href = window.location.pathname + '?' + newParams.substring(0, newParams.length-1);
        }
        else {
            window.location.href = window.location.pathname + '?' + param + '=' + value;
        }
    };

    /**
     * Custom filter function.
     *
     * @param widget_id
     * @param facet_id
     * @param option_id
     */
    CultuurnetWidgets.customFilter = function(widget_id ,facet_id, option_id) {
        // Change param to proper format.
        var param = 'facets[' + widget_id + '][custom][' + facet_id + '][' + option_id + ']';

        // Check for existing query parameters.
        var queryString = window.location.search;
        if (queryString) {
            // Convert existing query string to an object.
            var currentParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');

            if (typeof currentParams[param] !== 'undefined') {
                delete currentParams[param];
            }
            else {
                currentParams[param] = true;
            }

            // Build a query string from updated params.
            var newParams = CultuurnetWidgets.buildQueryUrl(currentParams);

            window.location.href = window.location.pathname + '?' + newParams.substring(0, newParams.length-1);
        } else {
            window.location.href = window.location.pathname + '?' + param + '=true';
        }
    };

    /**
     * Remove filter function.
     *
     * @param widget_id
     * @param key
     * @param option_id
     */
    CultuurnetWidgets.removeFilter = function(widget_id, key, option_id) {
        // Determine param in proper format (for regular or extra filters).
        var param = '';
        if (option_id !== null) {
            param = 'facets[' + widget_id + '][custom][' + key + '][' + option_id + ']';
        }
        else {
            param = 'facets[' + widget_id + '][' + key + ']';
        }

        // Check for existing query parameters.
        var queryString = window.location.search;

        // Convert existing query string to an object.
        var currentParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');

        // Delete corresponding parameter from URL.
        if (typeof currentParams[param] !== 'undefined') {
            delete currentParams[param];
        }

        // Build a query string from updated params.
        var newParams = CultuurnetWidgets.buildQueryUrl(currentParams);

        window.location.href = window.location.pathname + '?' + newParams.substring(0, newParams.length-1);
    };

})(CultuurnetWidgets, jQuery);
