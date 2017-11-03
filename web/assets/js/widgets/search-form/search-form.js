
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets) {

    'use strict';

    /**
     * Provide a behavior for search forms.
     */
    CultuurnetWidgets.behaviors.searchForm = {

        attach: function(context) {
            // Bind submit handlers on the search forms.
            jQuery(context).find('.cnw_form').each(CultuurnetWidgets.initSearchForm);
        }
    };

    /**
     * Init a search form.
     * @param $searchForm
     */
    CultuurnetWidgets.initSearchForm = function ($searchForm) {

        var $searchForm = jQuery(this);
        CultuurnetWidgets.setDefaultFormValues($searchForm);
        $searchForm.bind('submit', CultuurnetWidgets.submitSearchForm);

        var $customDateWrapper = $searchForm.find('.cnw_form-custom-date');
        if ($customDateWrapper.length) {

            var dateToString = function(date, format) {
                    // you should do formatting based on the passed format,
                    // but we will just return 'D/M/YYYY' for simplicity
                    const day = date.getDate();
                    const month = date.getMonth() + 1;
                    const year = date.getFullYear();
                    return day + '/' + month + '/' + year;
            };

            var stringToDate = function(date, format) {
                const parts = dateString.split('/');
                const day = parseInt(parts[0], 10);
                const month = parseInt(parts[1] - 1, 10);
                const year = parseInt(parts[1], 10);
                return new Date(year, month, day);
            };

            var $fromField = $customDateWrapper.find('.cnw_form-date-start');
            var $tillField = $customDateWrapper.find('.cnw_form-date-end');
            var pickerFrom = new Pikaday({
                field: $fromField[0],
                format: 'DD/MM/YYYY',
                toString: dateToString,
                parse: stringToDate
            });
            var pickerTill = new Pikaday({
                field: $tillField[0],
                format: 'DD/MM/YYYY',
                toString: dateToString,
                parse: stringToDate
            });

            $searchForm.find('.cnw_form-control-date').bind('change', function() {
                var $dateSelect = jQuery(this);
                if ($dateSelect.val() === 'custom_date') {
                    $customDateWrapper.show();
                }
                else {
                    $customDateWrapper.hide();
                    $fromField.val('');
                    $tillField.val('');
                }
            }).trigger('change');

        }

    }

    /**
     * Submit a search form
     *
     * @param widget_id
     * @param value
     * @param param
     */
    CultuurnetWidgets.submitSearchForm = function(e) {

        e.preventDefault();

        // Don't submit if there was an autocomplete open.
        if (!CultuurnetWidgets.autocompleteSubmit()) {
            return;
        }

        // Search all input fields.
        var paramsToSubmit = {};
        var paramsToDelete = {};
        jQuery(this).find(':input').each(function() {

            var $field = jQuery(this);

            if (!$field.attr('name')) {
                return true;
            }

            var value = $field.val();
            var checkboxes = {};
            // Text field => Just submit the entered value.
            if ($field.is(':text')) {
                if (value) {
                    paramsToSubmit[$field.attr('name')] = value;
                }
            }
            // Radios => Only submit the checked radios.
            else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    paramsToSubmit[$field.attr('name')] = value;
                }
            }
            // Checkboxes
            else if ($field.is(':checkbox')) {

                // Checked checkboxes => add a separator per value.
                if ($field.is(':checked')) {
                    if (paramsToSubmit[$field.attr('name')]) {
                        paramsToSubmit[$field.attr('name')] = paramsToSubmit[$field.attr('name')] + '|' + value;
                    }
                    else {
                        paramsToSubmit[$field.attr('name')] = value;
                    }
                }
                // Non checked checkbox. Make sure empty field is submitted, if no value was given yet.
                else {
                    if (paramsToSubmit[$field.attr('name')] === undefined) {
                        paramsToSubmit[$field.attr('name')] = '';
                    }
                }
            }
            // Other input => Just submit the value.
            else {
                paramsToSubmit[$field.attr('name')] = value;
            }

        });

        CultuurnetWidgets.redirectWithNewParams(paramsToSubmit);
    };

    /**
     * Set all correct default values for this form.
     */
    CultuurnetWidgets.setDefaultFormValues = function($searchForm) {

        var widgetId = $searchForm.data('widget-id');

        var currentParams = CultuurnetWidgets.getCurrentParams();
        if (currentParams) {
            // Search all input fields.
            $searchForm.find(':input').each(function() {

                var $field = jQuery(this);
                var fieldName = $field.attr('name');
                // If the field exists in query string, set the correct default value.
                if (currentParams[fieldName] !== undefined) {
                    // If field is a radio, deselect all + select the correct one.
                    if ($field.is(':radio')) {
                        $field.attr('checked', false);
                        $field.filter('[value="' + currentParams[fieldName] + '"]').attr('checked', true);
                    }
                    else if ($field.is(':checkbox')) {

                        // For checkboxes. Check all the one that were submitted.
                        $field.attr('checked', false);
                        var selectedOptions = currentParams[fieldName].split('|');
                        for (var key in selectedOptions) {
                            $field.filter('[value="' + selectedOptions[key] + '"]').attr('checked', true);
                        }

                    }
                    else {
                        $field.val(currentParams[fieldName]);
                    }
                }

            });
        }

    }

})(CultuurnetWidgets);
