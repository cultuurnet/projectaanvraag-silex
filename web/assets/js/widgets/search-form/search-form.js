
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets, $) {

    'use strict';

    /**
     * Provide a behavior for search forms.
     */
    CultuurnetWidgets.behaviors.searchForm = {

        attach: function(context) {
            // Bind submit handlers on the search forms.
            $(context).find('.cnw_form').each(CultuurnetWidgets.initSearchForm);
        }
    };

    /**
     * Init a search form.
     * @param $searchForm
     */
    CultuurnetWidgets.initSearchForm = function ($searchForm) {

        var $searchForm = $(this);
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
                var $dateSelect = $(this);
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

        var submittedValues = $(this).serializeArray();
        var paramsToSubmit = {};

        for (var submittedValue in submittedValues) {
            paramsToSubmit[submittedValues[submittedValue].name] = submittedValues[submittedValue].value;
        }

        CultuurnetWidgets.redirectWithNewParams(paramsToSubmit);
    };

    /**
     * Set all correct default values for this form.
     */
    CultuurnetWidgets.setDefaultFormValues = function($searchForm) {

        var widgetId = $searchForm.data('widget-id');

        var currentParams = JSON.parse('{"' + decodeURI(window.location.search.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');

        if (currentParams) {
            // Search all input fields.
            $searchForm.find(':input').each(function() {

                var $field = $(this);
                var fieldName = $field.attr('name');
                // If the field exists in query string, set the correct default value.
                if (currentParams[fieldName] !== undefined) {
                    // If field is a radio, deselect all + select the correct one.
                    if ($field.is(':radio')) {
                        $field.attr('checked', false);
                        $field.filter('[value="' + currentParams[fieldName] + '"]').attr('checked', true);
                    }
                    else {
                        $field.val(currentParams[fieldName]);
                    }
                }

            });
        }

    }

})(CultuurnetWidgets, jQuery);
