
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets, $) {

    'use strict';

    /**
     * Provide a behavior for facets.
     */
    CultuurnetWidgets.behaviors.facets = {

        attach: function(context) {

            CultuurnetWidgets.attachFacetsToggle(context);

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

                    if ($this.closest('li').hasClass('active') === true) {
                        if (type !== 'custom') {
                            CultuurnetWidgets.removeFacetFilter(widget_id, type, null);
                        }
                        else {
                            CultuurnetWidgets.removeCustomFilter(widget_id, facet_id, option_id);
                        }
                    }
                    else {
                        if (type !== 'custom') {
                            CultuurnetWidgets.facetFilter(widget_id, type, value, $this.text());
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
     * Attach the facets toggle to the context.
     * @param context
     */
    CultuurnetWidgets.attachFacetsToggle = function(context) {
        var $facetWrapper = $(context).find('.cnw_facets_content');
        if ($facetWrapper.length) {

            var $facetsToggle = $(context).find('.cnw_facets_toggle');

            $facetsToggle.bind('click', function(e) {
                e.preventDefault();
                $facetsToggle.toggleClass('cnw_facets_toggle--open');
                $facetWrapper.toggle();
                if ($facetsToggle.hasClass('cnw_facets_toggle--open')) {
                    $facetsToggle.html('Verberg verfijningen');
                }
                else {
                    $facetsToggle.html('Toon verfijningen');
                }
            });
            
            // Facets are default hidden for mobile.
            if ($(window).width() < 768) {
                $facetsToggle.trigger('click');
            }

        }
    };

    /**
     * Facet filter function.
     *
     * @param widget_id
     * @param value
     * @param param
     */
    CultuurnetWidgets.facetFilter = function(widget_id, param, value, label) {


        var paramsToSubmit = {};
        paramsToSubmit['facets[' + widget_id + '][' + param + '][' + value + ']'] = label;

        var params = CultuurnetWidgets.getCurrentParams();
        for (var key in params) {
            if (!key.includes('facets[' + widget_id + '][' + param + ']')) {
                paramsToSubmit[key] = params[key]
            }
        }

        window.location.href = window.location.pathname + '?' + CultuurnetWidgets.buildQueryUrl(paramsToSubmit);
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
        var paramsToSubmit = {};
        paramsToSubmit[param] = 'true';
        CultuurnetWidgets.redirectWithNewParams(paramsToSubmit);
    };

    /**
     * Remove a custom filter.
     *
     * @param widget_id
     * @param key
     * @param option_id
     */
    CultuurnetWidgets.removeCustomFilter = function(widget_id, key, option_id) {
        var paramsToDelete = [
            'facets[' + widget_id + '][custom][' + key + '][' + option_id + ']'
        ];
        CultuurnetWidgets.redirectAndDeleteParams(paramsToDelete);
    };

    /**
     * Remove a real facet.
     *
     * @param widget_id
     * @param facet_type
     */
    CultuurnetWidgets.removeFacetFilter = function(widget_id, facet_type) {

        var params = CultuurnetWidgets.getCurrentParams();
        var paramsToDelete = [];
        for (var key in params) {
            if (key.includes('facets[' + widget_id + '][' + facet_type + ']')) {
                paramsToDelete.push(key);
            }
        }

        CultuurnetWidgets.redirectAndDeleteParams(paramsToDelete);
    };

})(CultuurnetWidgets, jQuery);
