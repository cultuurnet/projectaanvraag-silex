
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets, $) {

    'use strict';

    /**
     * Provide a behavior for pagers.
     */
    CultuurnetWidgets.behaviors.pager = {

        attach: function(context) {
            //
        }

    };

    /**
     * Temporary pager switch function.
     * TODO: this is probably not the right way?
     *
     * @param page
     */
    CultuurnetWidgets.switchPage = function(page) {
        var queryString = window.location.search;

        if (queryString) {
            // Convert existing query string to an object.
            var currentParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g,"\":\"")) + '"}');

            if (typeof currentParams.page !== 'undefined' && page === 0) {
                delete currentParams.page;
            }
            else {
                currentParams.page = page;
            }

            // Convert updated params to a query string.
            var newParams = $.param(currentParams);

            // TODO: This should eventually work asynchronously (using $.ajax ?).
            window.location.href = window.location.pathname + '?' + newParams;
        } else {
            // Refresh page with a page param.
            window.location.href = window.location.pathname + '?page=' + page;
        }
    };

})(CultuurnetWidgets, jQuery);
