
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets, $) {

    'use strict';

    /**
     * Provide a behavior for facets.
     */
    CultuurnetWidgets.behaviors.facets = {

        attach: function(context) {
            //
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

})(CultuurnetWidgets, jQuery);
