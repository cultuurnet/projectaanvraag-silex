
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets, $) {

    'use strict';

    CultuurnetWidgets.id = '';

    /**
     * Provide a behavior for facets.
     */
    CultuurnetWidgets.behaviors.facets = {

        attach: function(context) {
            CultuurnetWidgets.id = $(context).find('[data-widget-id]').data('widget-id');
            // Click event binding for facet filters.
            $(context).find('a[data-facet-type]').each(function() {
                $(this).bind('click', function() {
                    var type = $(this).data('facet-type');
                    var value = $(this).data('facet-value');

                    if (type !== 'extra') {
                        CultuurnetWidgets.facetFilter('facet_' + type, value);
                    }
                    else {
                        CultuurnetWidgets.extraFilter(value);
                    }
                });
            });
        }
    };

    /**
     * Facet filter function.
     *
     * @param value
     * @param param
     */
    CultuurnetWidgets.facetFilter = function(param, value) {
        var queryString = window.location.search;

        if (queryString) {
            // Convert existing query string to an object.
            var currentParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');
            // Change param to proper format.
            param = 'facets[' + CultuurnetWidgets.id + '][' + param + ']';

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
        } else {
            window.location.href = window.location.pathname + '?facets[' + CultuurnetWidgets.id + '][' + param + ']=' + value;
        }
    };

    /**
     * Extra filter function.
     *
     * @param query
     */
    CultuurnetWidgets.extraFilter = function(query) {
        var queryString = window.location.search;

        if (queryString) {
            // Convert existing query string to an object.
            var currentParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');

            // Split query value.
            var split = query.split('=');

            var param = split[0];
            var value = split[1];

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
        } else {
            window.location.href = window.location.pathname + '?' + query;
        }
    };

})(CultuurnetWidgets, jQuery);
