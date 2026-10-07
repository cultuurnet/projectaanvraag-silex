
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets) {

    'use strict';

    /**
     * Provide a behavior for the age filter of search forms.
     */
    CultuurnetWidgets.behaviors.ageFilter = {

        attach: function(context) {
            jQuery(context).find('.cnw_age-filter').each(CultuurnetWidgets.initAgeFilter);
        }
    };

    /**
     * Init an age filter.
     */
    CultuurnetWidgets.initAgeFilter = function () {

        var $ageFilter = jQuery(this);

        if ($ageFilter.data('age-filter-attached')) {
            return;
        }
        $ageFilter.data('age-filter-attached', true);

        var $trigger = $ageFilter.find('.cnw_age-filter-trigger');
        var $dropdown = $ageFilter.find('.cnw_age-filter-dropdown');
        var $modeButtons = $ageFilter.find('.cnw_age-filter-mode');
        var $modeField = $ageFilter.find('.cnw_age-filter-mode-input');

        var toggleDropdown = function(visible) {
            $dropdown.toggle(visible);
            $trigger.attr('aria-expanded', visible);
        };

        $trigger.bind('click', function() {
            toggleDropdown(!$dropdown.is(':visible'));
        });

        $ageFilter.find('.cnw_age-filter-confirm').bind('click', function() {
            toggleDropdown(false);
        });

        jQuery(document).bind('click', function(e) {
            if (!jQuery(e.target).closest($ageFilter).length) {
                toggleDropdown(false);
            }
        });

        var applyMode = function(mode) {
            $modeButtons.removeClass('cnw_age-filter-mode--active');
            $modeButtons.filter('[data-age-filter-mode="' + mode + '"]').addClass('cnw_age-filter-mode--active');

            $ageFilter.find('[data-age-label]').each(function() {
                jQuery(this).text(jQuery(this).data(mode + '-label'));
            });

            $modeField.val(mode);
            CultuurnetWidgets.updateAgeFilterSummary($ageFilter);
        };

        $modeButtons.bind('click', function() {
            var mode = jQuery(this).data('age-filter-mode');

            if (mode !== $modeField.val()) {
                $ageFilter.find(':checkbox').prop('checked', false);
            }

            applyMode(mode);
        });

        $ageFilter.find(':checkbox').bind('change', function() {
            CultuurnetWidgets.updateAgeFilterSummary($ageFilter);
        });

        applyMode($modeField.val());
    };

    /**
     * Show the selected ages on the closed field.
     *
     * @param $ageFilter
     */
    CultuurnetWidgets.updateAgeFilterSummary = function($ageFilter) {

        var labels = $ageFilter.find(':checkbox:checked').map(function() {
            return jQuery(this).siblings('.cnw_age-filter-option-label').text();
        }).get();

        $ageFilter.find('.cnw_age-filter-summary').text(
            labels.length ? labels.join(', ') : $ageFilter.find('.cnw_age-filter-trigger').data('placeholder')
        );
    };

})(CultuurnetWidgets);
