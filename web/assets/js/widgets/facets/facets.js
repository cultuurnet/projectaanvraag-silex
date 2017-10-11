
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
                $(this).bind('click', function() {
                    var widget_id = $(this).parents('[data-widget-id]').data('widget-id');
                    var type = $(this).data('facet-type');
                    var value = $(this).data('facet-value');

                    if (type !== 'extra') {
                        CultuurnetWidgets.facetFilter(widget_id, 'facet-' + type, value);
                    }
                    else {
                        var facet_id = $(this).data('facet-id');
                        var option_id = $(this).data('facet-option-id');
                        CultuurnetWidgets.extraFilter(widget_id, facet_id, option_id);
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
            var newParams = '';
            for (var key in currentParams) {
                newParams += key + '=' + currentParams[key] + '&';
            }

            window.location.href = window.location.pathname + '?' + newParams.substring(0, newParams.length-1);
        }
        else {
            window.location.href = window.location.pathname + '?' + param + '=' + value;
        }
    };

    /**
     * Extra filter function.
     *
     * @param widget_id
     * @param facet_id
     * @param option_id
     */
    CultuurnetWidgets.extraFilter = function(widget_id ,facet_id, option_id) {
        // Change param to proper format.
        var param = 'facets[' + widget_id + '][extra][' + facet_id + '][' + option_id + ']';

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
            var newParams = '';
            for (var key in currentParams) {
                newParams += key + '=' + currentParams[key] + '&';
            }

            window.location.href = window.location.pathname + '?' + newParams.substring(0, newParams.length-1);
        } else {
            window.location.href = window.location.pathname + '?' + param + '=true';
        }
    };

})(CultuurnetWidgets, jQuery);
