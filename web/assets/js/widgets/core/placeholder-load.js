
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets) {

    'use strict';

    /**
     * Provide a behavior to load all content of placeholders.
     */
    CultuurnetWidgets.behaviors.placeholders = {

        attach: function(context, widgetPageId) {

            jQuery(context).find('[data-widget-placeholder-id]').each(function() {

                var currentParams = CultuurnetWidgets.getCurrentParams();
                var $placeholder = jQuery(this);
                if ($placeholder.data('widget-autoload')) {

                    if ($placeholder.data('widget-type') !== 'search-results') {
                        if($placeholder.data('widget-type') == 'tips' && $placeholder.parents('#embed').data('cdbid') !== undefined) {
                          var widgetId = jQuery(this).data('widget-placeholder-id');
                          var cdbid = $placeholder.parents('#embed').data('cdbid');
                          CultuurnetWidgets.renderTipsEmbed(widgetId, widgetPageId, cdbid).then(function(response) {
                              $placeholder.html(response.data);
                              CultuurnetWidgets.attachBehaviors($placeholder, widgetPageId);
                          });
                        } else {
                          CultuurnetWidgets.renderWidget(jQuery(this).data('widget-placeholder-id'), widgetPageId).then(function(response) {
                              $placeholder.html(response.data);
                              CultuurnetWidgets.attachBehaviors($placeholder, widgetPageId);
                          });
                        }
                    }
                    // For performance reasons, search results have a separate call to render the search result + all related facets via 1 call.
                    else {

                        var widgetId = jQuery(this).data('widget-placeholder-id');

                        if (currentParams['cdbid'] && CultuurnetWidgetsSettings[widgetPageId].detailPageWidgetId == widgetId) {

                            // Remove any remaining facet that could be in a complete other region.
                            jQuery(context).find('[data-widget-facet-target="' + widgetId + '"]').html('');

                            CultuurnetWidgets.renderDetailPage(widgetId, widgetPageId).then(function(response) {
                                $placeholder.html(response.data);
                                CultuurnetWidgets.attachBehaviors($placeholder, widgetPageId);
                            });
                        }
                        else {
                            CultuurnetWidgets.renderSearchResults(widgetId, widgetPageId).then(function(response) {
                                $placeholder.html(response.data.search_results);
                                CultuurnetWidgets.attachBehaviors($placeholder, widgetPageId);
                                for (var facet_id in response.data.facets) {
                                    var $facet_placeholder = jQuery(context).find('[data-widget-placeholder-id="' + facet_id + '"]');
                                    $facet_placeholder.html(response.data.facets[facet_id]);
                                    CultuurnetWidgets.attachBehaviors($facet_placeholder, widgetPageId);
                                }

                            });
                        }

                    }
                }
            })

        }


    };

})(CultuurnetWidgets);
