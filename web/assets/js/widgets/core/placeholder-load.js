
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

                CultuurnetWidgets.renderWidget($(this).data('widget-placeholder-id')).then(function(response) {
                    $placeholder.html(response.data);
                });
            })

        }


    };

})(CultuurnetWidgets, jQuery);