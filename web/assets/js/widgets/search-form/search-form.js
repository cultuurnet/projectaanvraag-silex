
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
        var $submitButton = $searchForm.find(".cnw_btn-search");
        $searchForm.bind('submit', CultuurnetWidgets.submitSearchForm);
        CultuurnetWidgets.setDefaultFormValues($searchForm);
        $submitButton.bind('click', function() {
          $searchForm.submit();
        });

        var $customDateWrapper = $searchForm.find('.cnw_form-custom-date');
        if ($customDateWrapper.length) {

            var dateToString = function(date, format) {
                    // you should do formatting based on the passed format,
                    // but we will just return 'D/M/YYYY' for simplicity
                    var day = date.getDate();
                    var month = date.getMonth() + 1;
                    var year = date.getFullYear();
                    return day + '/' + month + '/' + year;
            };

            var stringToDate = function(date, format) {
                var parts = date.split('/');
                var day = parseInt(parts[0], 10);
                var month = parseInt(parts[1] - 1, 10);
                var year = parseInt(parts[2], 10);
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
        var $form = jQuery(this);

        var noCitySelected = $form.find('.city-not-selected');

        if (noCitySelected.length > 0) {
          noCitySelected.addClass('cnw_form-control-danger');
          noCitySelected
            .parent()
            .addClass('cnw_has-danger')
            .find('.cnw_form-control-feedback')
            .removeClass('element-invisible')
          return; // Don't submit
        }

        var openInNewWindow = $form.data('widget-new-window');

        $form.find(':input').each(function() {

            var $field = jQuery(this);

            if (!$field.attr('name')) {
                return true;
            }

            var value = $field.val();

            // Text field => Just submit the entered value.
            if ($field.is(':text')) {
                if (value) {
                    paramsToSubmit[$field.attr('name')] = encodeURIComponent(value);
                }
            }
            // Radios => Only submit the checked radios.
            else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    paramsToSubmit[$field.attr('name')] = value;
                } else {
                  paramsToSubmit[$field.attr('name')] = 'delete-param';
                }
            }
            // Checkboxes
            else if ($field.is(':checkbox')) {
                // Checked checkboxes => add a separator per value.
                if ($field.is(':checked')) {
                    if (typeof paramsToSubmit[$field.attr('name')] != 'undefined') {
                        paramsToSubmit[$field.attr('name')] = (paramsToSubmit[$field.attr('name')] + '|' + value);
                    }
                    else {
                        paramsToSubmit[$field.attr('name')] = value;
                    }
                }
            }
            // Other input => Submit the value if it is different than the default value.
            else {

                var defaultValue = $field.data('default-value');
                if (value === "") {
                    if (defaultValue != 'placeholder' && defaultValue != -1) {
                        paramsToSubmit[$field.attr('name')] = value;
                    }
                }
                else {
                    if (defaultValue !== value) {
                        paramsToSubmit[$field.attr('name')] = value;
                    }
                }

            }

        });

        var destination = $form.data('widget-destination');
        if (destination) {
            var pageId = $form.closest(".cultuurnet-widgets").first().data("widgetPageId");
            paramsToSubmit['submitted_page'] = CultuurnetWidgetsSettings[pageId].widgetPageId;
        }

        CultuurnetWidgets.redirectWithNewParams(paramsToSubmit, openInNewWindow, destination, true);
    };

    /**
     * Set all correct default values for this form.
     */
    CultuurnetWidgets.setDefaultFormValues = function($searchForm) {

        var widgetId = $searchForm.data('widget-id');

        var currentParams = CultuurnetWidgets.getCurrentParams(true);
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
                        if (fieldName.includes('[date-start]') || fieldName.includes('[date-end]')) {
                            var formattedDate = decodeURIComponent(currentParams[fieldName]);
                            var parts = formattedDate.split('/');
                            var day = parseInt(parts[0], 10);
                            var month = parseInt(parts[1] - 1, 10);
                            var year = parseInt(parts[2], 10);
                            $field.val(new Date(year, month, day));
                        }
                        else {
                            $field.val(decodeURIComponent(currentParams[fieldName]));
                        }
                    }
                }
            });
        }

    }

})(CultuurnetWidgets);
