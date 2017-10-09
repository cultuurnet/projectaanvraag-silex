
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets, $) {

    'use strict';

    /**
     * Provide a behavior to load all content of placeholders.
     */
    CultuurnetWidgets.behaviors.placeholders = {

        attach: function(context) {

            $(context).find('[data-widget-placeholder-id]').each(function() {

                var $placeholder = $(this);
                if ($placeholder.data('widget-autoload')) {
                    if ($placeholder.data('widget-type') !== 'search-results') {
                        CultuurnetWidgets.renderWidget($(this).data('widget-placeholder-id')).then(function(response) {
                            $placeholder.html(response.data);
                        });
                    }
                    // For performance reasons, search results have a separate call to render the search result + all related facets via 1 call.
                    else {
                        CultuurnetWidgets.renderSearchResults($(this).data('widget-placeholder-id')).then(function(response) {
                            $placeholder.html(response.data.search_results);
                            for (var facet_id in response.data.facets) {
                                $(context).find('[data-widget-placeholder-id="' + facet_id + '"]').html(response.data.facets[facet_id]);
                            }

                        });
                    }
                }
            })

        }


    };

})(CultuurnetWidgets, jQuery);