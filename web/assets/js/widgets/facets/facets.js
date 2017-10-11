
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
                        var facet_id = $(this).data('facet-id');
                        var option_id = $(this).data('facet-option-id');
                        CultuurnetWidgets.extraFilter(facet_id, option_id);
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
        // Change param to proper format.
        param = 'facets[' + CultuurnetWidgets.id + '][' + param + ']';

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
     * @param facet_id
     * @param option_id
     */
    CultuurnetWidgets.extraFilter = function(facet_id, option_id) {
        // Change param to proper format.
        var param = 'facets[' + CultuurnetWidgets.id + '][extra][' + facet_id + '][' + option_id + ']';

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
