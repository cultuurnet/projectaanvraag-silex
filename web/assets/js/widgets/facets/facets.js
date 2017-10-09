
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
                    var type = $(this).attr('data-facet-type');
                    var value = $(this).attr('data-facet-value');

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

            if (typeof currentParams[param] !== 'undefined' && value === 0) {
                delete currentParams[param];
            }
            else {
                currentParams[param] = value;
            }

            // Convert updated params to a query string.
            var newParams = $.param(currentParams);

            // TODO: Should this eventually work asynchronously?.
            window.location.href = window.location.pathname + '?' + newParams;
        } else {
            window.location.href = window.location.pathname + '?' + param + '=' + value;
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

            // Convert updated params to a query string.
            var newParams = $.param(currentParams);

            // TODO: Should this eventually work asynchronously?.
            window.location.href = window.location.pathname + '?' + newParams;
        } else {
            window.location.href = window.location.pathname + '?' + query;
        }
    };

})(CultuurnetWidgets, jQuery);
