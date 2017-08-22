
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
     * Temporary facet date filter function.
     * TODO: this is probably not the right way?
     *
     * @param date
     */
    CultuurnetWidgets.facetDateFilter = function(date) {
        var queryString = window.location.search;

        if (queryString) {
            // Convert existing query string to an object.
            var currentParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');

            if (typeof currentParams.date !== 'undefined' && date === 0) {
                delete currentParams.date;
            }
            else {
                currentParams.date = date;
            }

            // Convert updated params to a query string.
            var newParams = $.param(currentParams);

            // TODO: This should eventually work asynchronously (using $.ajax ?).
            window.location.href = window.location.pathname + '?' + newParams;
        } else {
            window.location.href = window.location.pathname + '?date=' + date;
        }
    };

    /**
     * Temporary facet region filter function.
     * TODO: this is probably not the right way?
     *
     * @param region
     */
    CultuurnetWidgets.facetRegionFilter = function(region) {
        var queryString = window.location.search;

        if (queryString) {
            // Convert existing query string to an object.
            var currentParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');

            if (typeof currentParams.region !== 'undefined' && region === 0) {
                delete currentParams.region;
            }
            else {
                currentParams.region = region;
            }

            // Convert updated params to a query string.
            var newParams = $.param(currentParams);

            // TODO: This should eventually work asynchronously (using $.ajax ?).
            window.location.href = window.location.pathname + '?' + newParams;
        } else {
            window.location.href = window.location.pathname + '?region=' + region;
        }
    };

})(CultuurnetWidgets, jQuery);
