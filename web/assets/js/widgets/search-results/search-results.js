
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets) {

    'use strict';

    /**
     * Provide a behavior for search results.
     */
    CultuurnetWidgets.behaviors.searchResults = {

        attach: function(context) {

            // Attach the pager.
            CultuurnetWidgets.bindPager(context);

            // Bind extra filters.
            CultuurnetWidgets.bindSearchResultFilters(context);

            // Bind keyword removal.
            CultuurnetWidgets.bindRemoveActiveFilters(context);

            // Bind summary toggle.
            CultuurnetWidgets.bindSummaryToggle(context);

            // Bind facility toggle.
            CultuurnetWidgets.bindFacilityToggle(context);

        }

    };

    /**
     * Bind the pager click listeners.
     */
    CultuurnetWidgets.bindPager = function(context) {
        jQuery(context).find('a[data-target-page]').each(function() {

            var $this = jQuery(this);
            var targetWidget = $this.data('target-widget');
            if (targetWidget != undefined) {

                // Clicking on a link should redirect the user to the correct page.
                $this.bind('click', function(e) {
                    e.preventDefault();

                    var targetPage = $this.data('target-page');

                    var paramsToSubmit = CultuurnetWidgets.getCurrentParams();
                    paramsToSubmit['search-result[' + targetWidget + '][page]'] = targetPage;
                    CultuurnetWidgets.redirectWithNewParams(paramsToSubmit);

                });
            }

        });
    };

    /**
     * Bind the extra search result filters
     */
    CultuurnetWidgets.bindSearchResultFilters = function (context) {
        jQuery(context).find('.cnw_searchresult__options').find(':input').bind('change', function() {

            var $this = jQuery(this);
            if ($this.is(':checked')) {
                var paramsToSubmit = {};
                paramsToSubmit[$this.attr('name')] = true;
                CultuurnetWidgets.redirectWithNewParams(paramsToSubmit);
            }
            else {
                var paramsToDelete = [$this.attr('name')];
                CultuurnetWidgets.redirectAndDeleteParams(paramsToDelete);
            }

        });
    };


    /**
     * Bind the removal links for active filters
     */
    CultuurnetWidgets.bindRemoveActiveFilters = function (context) {
        var $activeFilters = jQuery(context).find('.cnw_searchresult__searchwords').find('[data-active-keywords-name]');
        $activeFilters.bind('click', function(e) {

            e.preventDefault();

            var $this = jQuery(this);

            var paramToDelete = $this.data('active-keywords-name');
            // User requests to delete all widgets.
            if (paramToDelete == 'all') {

                var params = [];
                // Create a new list of params. Filters marked as default should stay in url
                // but with an empty value
                $activeFilters.each(function() {
                    if (jQuery(this).data('active-keywords-default-option') === 1) {
                        params[jQuery(this).data('active-keywords-name')] = '';
                    }
                });

                window.location.href = window.location.pathname + '?' + CultuurnetWidgets.buildQueryUrl(params);

            }
            else {

                // If this was a default option. Keep it in url but with empty value.
                if ($this.data('active-keywords-default-option') === 1) {
                    var params = [];
                    params[$this.data('active-keywords-name')] = '';
                    CultuurnetWidgets.redirectWithNewParams(params);
                }
                else {
                    var paramsToDelete = [paramToDelete];
                    CultuurnetWidgets.redirectAndDeleteParams(paramsToDelete);
                }
            }

        });
    };

    /**
     * Bind the summary toggle link and hide the full description by default.
     * @param context
     */
    CultuurnetWidgets.bindSummaryToggle = function(context) {

        var $context = jQuery(context);
        var $summaryToggle = jQuery(context).find('a.cnw_detail-summary-toggle');
        if ($summaryToggle.length > 0) {
            var $fullDescription = $context.find('.cnw_detail-full-description');
            $fullDescription.hide();
            $summaryToggle.bind('click', function(e) {
                jQuery(this).hide();
                $context.find('.cnw_detail-short-summary').hide();
                $fullDescription.show();
                e.preventDefault();
            })
        }

    };

    /**
     * Bind the summary toggle link and hide the full description by default.
     * @param context
     */
    CultuurnetWidgets.bindFacilityToggle = function(context) {

        var $context = jQuery(context);
        var $summaryToggle = jQuery(context).find('a.cnw_facility-toggle');
        if ($summaryToggle.length > 0) {
            var $fullFacilities = $context.find('.cnw_full-facilities');
            $fullFacilities.hide();
            $summaryToggle.bind('click', function(e) {
                jQuery(this).hide();
                $fullFacilities.show();
                e.preventDefault();
            })
        }

    };

})(CultuurnetWidgets);
