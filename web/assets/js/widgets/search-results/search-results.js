
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets, $) {

    'use strict';

    /**
     * Provide a behavior for search results.
     */
    CultuurnetWidgets.behaviors.searchResults = {

        attach: function(context) {

            // Bind the pager.
            $(context).find('a[data-target-page]').each(function() {

                var $this = $(this);
                var targetWidget = $this.data('target-widget');
                if (targetWidget != undefined) {

                    // Clicking on a link should redirect the user to the correct page.
                    $this.bind('click', function(e) {
                        e.preventDefault();

                        var targetPage = $this.data('target-page');

                        var paramsToSubmit = {};
                        paramsToSubmit['page[' + targetWidget + ']'] = targetPage;
                        CultuurnetWidgets.redirectWithNewParams(paramsToSubmit);

                    });
                }

            });
        }

    };

})(CultuurnetWidgets, jQuery);
