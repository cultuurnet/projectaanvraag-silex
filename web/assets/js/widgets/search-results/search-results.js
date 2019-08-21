
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

            // Bind Event Date toggle
            CultuurnetWidgets.bindEventDatesToggle(context);
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
                CultuurnetWidgets.redirectWithNewParams(paramsToSubmit, false, false, true);
            }
            else {
                var paramsToDelete = [$this.attr('name')];
                CultuurnetWidgets.redirectAndDeleteParams(paramsToDelete, true);
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
                    CultuurnetWidgets.redirectWithNewParams(params, false, false, true);
                }
                else {
                    var paramsToDelete = [paramToDelete];
                    CultuurnetWidgets.redirectAndDeleteParams(paramsToDelete, true);
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
        var $facilityToggle = jQuery(context).find('a.cnw_facility-toggle');
        if ($facilityToggle.length > 0) {
            $facilityToggle.bind('click', function(e) {
                var $fullFacilities = jQuery(this).parent().find('.cnw_full-facilities');
                $fullFacilities.toggleClass("show");
                e.preventDefault();
            });
        }

    };

    /**
     * Bind the event dates toggle link and only show 5 dates by default.
     * @param context
     */
    CultuurnetWidgets.bindEventDatesToggle = function(context) {

        var maxDatesVisible = 5; // define how many upcoming dates to show by default
        var $eventDatesContainer = jQuery(context).find('.cnw-event-date-container');
        var $eventDates = jQuery(context).find('ul.cnw-event-date-info');
        var $eventDateItems = $eventDates.find('li');
        var $eventDateToggler = jQuery(context).find('.cnw-event-date-info-toggle');
        if ($eventDatesContainer && $eventDates && $eventDateItems.length > maxDatesVisible) {
         $eventDateToggler.removeClass('cnw_hidden'); 
         $eventDateToggler.bind('click', function(e){
            e.preventDefault();
            $eventDates.toggleClass('open');
            $(this).toggleClass('open');
         }); 
        }

    };

})(CultuurnetWidgets);
