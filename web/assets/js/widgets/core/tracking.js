(function (CultuurnetWidgets, $) {

    /**
     * Load google tag manager.
     */
    CultuurnetWidgets.initTagManager = function() {

        // Only load the tag manager if we have an id.
        if (CultuurnetWidgetsSettings.googleTagManagerId && typeof cnWidgetsDataLayer == 'undefined') {
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','cnWidgetsDataLayer', CultuurnetWidgetsSettings.googleTagManagerId);
        }

    };

    /**
     * Provide a behavior to load all content of placeholders.
     */
    CultuurnetWidgets.behaviors.tracking = {

        attach: function (context) {

            // If context contains a widget. Track the correct event related with it.
            $(context).find('[data-view-tracking-category]').each(function () {
                var $widget = $(this);
                console.log($widget);
                CultuurnetWidgets.trackEvent('widgetLoaded', 'view', $widget.data('view-tracking-category'), $widget.data('view-tracking-page-suffix'), $widget.data('view-tracking-extra-data'));
            });

            // Add click tracking on the needed links.
            $(context).find('[data-click-tracking-category]').bind('click', function (e) {
                var $clickedElement = $(this);
                var category = $clickedElement.data('click-tracking-category');
                var action = $clickedElement.data('click-tracking-action');

                if (category && action) {

                    var label = $clickedElement.data('data-click-tracking-label');

                    var extra_gtm_data = {};
                    if (label) {
                        extra_gtm_data['event-label'] = label;
                    }

                    CultuurnetWidgets.trackEvent('trackEvent', action, category, '', extra_gtm_data);
                }
            });

            // The uiv link is part of the event description in API.
            // Attach custom tracking on it.
            $(context).find('.uiv-source').find('a').bind('click', function() {
                CultuurnetWidgets.trackEvent('trackEvent', 'source', 'detail');
            })

        }

    };

    /**
     * Track an event via tag manager.
     * @param action
     * @param category
     * @param title
     */
    CultuurnetWidgets.trackEvent = function (event, action, category, page_suffix, extra_gtm_data) {

        if (!cnWidgetsDataLayer) {
            return;
        }

        var page = CultuurnetWidgetsSettings.consumerName;
        if (page_suffix) {
            page = CultuurnetWidgetsSettings.consumerName + page_suffix;
        }

        var gtm_data = {
            'event': event,
            'event-action': action.toLowerCase(),
            'event-category': category.toLowerCase(),
            'consumer_key': CultuurnetWidgetsSettings.consumerKey,
            'consumer_name': CultuurnetWidgetsSettings.consumerName,
            'page': page,
        };

        if (extra_gtm_data) {

            // If pageTitle is given. Prefix it with the widget page title.
            if (extra_gtm_data.pageTitleSuffix) {
                extra_gtm_data.pageTitle = CultuurnetWidgetsSettings.widgetPageTitle + '|' + extra_gtm_data.pageTitleSuffix;
            }

            gtm_data = Object.assign(gtm_data, extra_gtm_data);
        }

        cnWidgetsDataLayer.push(gtm_data);
    }

})(CultuurnetWidgets, jQuery);