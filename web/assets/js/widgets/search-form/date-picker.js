
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets) {

    'use strict';

    /**
     * Provide a behavior for the date picker of search forms.
     */
    CultuurnetWidgets.behaviors.datePicker = {

        attach: function(context, widgetPageId) {
            var holidaysUrl = (CultuurnetWidgetsSettings[widgetPageId] || {}).holidaysUrl;

            jQuery(context).find('.cnw_date-picker').each(function() {
                CultuurnetWidgets.initDatePicker(jQuery(this), holidaysUrl);
            });
        }
    };

    /**
     * Init a date picker.
     *
     * @param $datePicker
     * @param holidaysUrl
     */
    CultuurnetWidgets.initDatePicker = function ($datePicker, holidaysUrl) {

        if ($datePicker.data('date-picker-attached')) {
            return;
        }
        $datePicker.data('date-picker-attached', true);

        var language = $datePicker.data('language');
        var $trigger = $datePicker.find('.cnw_date-picker-trigger');
        var $dropdown = $datePicker.find('.cnw_date-picker-dropdown');
        var $calendar = $datePicker.find('.cnw_date-picker-calendar');
        var $quickLinks = $datePicker.find('.cnw_date-picker-quick-links');
        var $holidaysList = $datePicker.find('.cnw_date-picker-holidays');
        var $holidaysToggle = $datePicker.find('.cnw_date-picker-holidays-toggle input');
        var $modes = $datePicker.find('.cnw_date-picker-mode');
        var $days = $datePicker.find('.cnw_date-picker-day input');
        var $times = $datePicker.find('.cnw_date-picker-time input');
        var selection = { when: '', start: null, end: null };
        var holidays = [];
        var holidaysLoadedUntil = null;
        var showSchoolHolidays = $datePicker.data('school-holidays') === true;

        var today = new Date();
        today.setHours(0, 0, 0, 0);

        var getDayDate = function($day) {
            return new Date($day.data('pika-year'), $day.data('pika-month'), $day.data('pika-day'));
        };

        var renderHolidayQuickLinks = function() {
            if (!showSchoolHolidays) {
                return;
            }

            $quickLinks.find('.cnw_date-picker-quick-link:not([data-when])').remove();

            holidays.filter(function(holiday) {
                return holiday.isSchoolHoliday;
            }).slice(0, 3).forEach(function(holiday) {
                jQuery('<button type="button" class="cnw_btn cnw_date-picker-quick-link"></button>')
                    .text(holiday.name)
                    .data('start', holiday.start < today ? today : holiday.start)
                    .data('end', holiday.end)
                    .bind('click', selectQuickLink)
                    .appendTo($quickLinks);
            });
        };

        var shouldLoadHolidays = function() {
            return showSchoolHolidays || $holidaysToggle.is(':checked');
        };

        var loadHolidays = function(untilDate) {
            if (!holidaysUrl || !shouldLoadHolidays() || (holidaysLoadedUntil && untilDate <= holidaysLoadedUntil)) {
                return;
            }

            var startDate = holidaysLoadedUntil ? new Date(holidaysLoadedUntil.getFullYear() + 1, 0, 1) : today;
            holidaysLoadedUntil = new Date(untilDate.getFullYear(), 11, 31);

            var parameters = {
                startDate: CultuurnetWidgets.formatDatePickerIsoDate(startDate),
                endDate: CultuurnetWidgets.formatDatePickerIsoDate(holidaysLoadedUntil)
            };

            jQuery.getJSON(holidaysUrl, parameters, function(data) {
                CultuurnetWidgets.parseDatePickerHolidays(data, language).forEach(function(holiday) {
                    var isAlreadyLoaded = holidays.some(function(loadedHoliday) {
                        return CultuurnetWidgets.isSameDay(loadedHoliday.start, holiday.start) && loadedHoliday.name === holiday.name;
                    });

                    if (!isAlreadyLoaded) {
                        holidays.push(holiday);
                    }
                });
                holidays.sort(function(a, b) {
                    return a.start - b.start;
                });

                renderHolidayQuickLinks();
                select(selection.when, selection.start, selection.end);
            });
        };

        var drawHolidays = function(pikaday) {
            var visibleMonth = pikaday.calendars[0];
            var firstDayOfMonth = new Date(visibleMonth.year, visibleMonth.month, 1);
            var lastDayOfMonth = new Date(visibleMonth.year, visibleMonth.month + 1, 0);

            if ($dropdown.is(':visible')) {
                loadHolidays(lastDayOfMonth);
            }
            $holidaysList.empty();

            if (!$holidaysToggle.is(':checked')) {
                return;
            }

            jQuery(pikaday.el).find('.pika-button').each(function() {
                var $day = jQuery(this);
                var date = getDayDate($day);
                var isWeekend = date.getDay() === 0 || date.getDay() === 6;
                var isHoliday = holidays.some(function(holiday) {
                    return holiday.start <= date && date <= holiday.end;
                });

                $day.parent().toggleClass('is-holiday', isWeekend || isHoliday);
            });

            var formatDay = function(date) {
                return date.toLocaleDateString(language, { day: 'numeric', month: 'long' });
            };

            holidays.forEach(function(holiday) {
                if (holiday.start <= lastDayOfMonth && holiday.end >= firstDayOfMonth) {
                    var period = formatDay(holiday.start) + (holiday.end > holiday.start ? ' - ' + formatDay(holiday.end) : '');
                    $holidaysList.append(jQuery('<li>').text(period + ': ' + holiday.name));
                }
            });
        };

        var picker = new Pikaday({
            bound: false,
            keyboardInput: false,
            minDate: today,
            i18n: CultuurnetWidgets.datePickerI18n(language),
            onDraw: drawHolidays,
            onSelect: function(date) {
                var isChoosingEndDate = selection.when === 'custom_date' && !selection.end && date >= selection.start;

                if (isChoosingEndDate) {
                    select('custom_date', selection.start, date);
                }
                else {
                    select('custom_date', date, null);
                }
            }
        });
        $calendar.prepend(picker.el);

        var focusDay = function(date) {
            jQuery(picker.el).find('[data-pika-month="' + date.getMonth() + '"][data-pika-day="' + date.getDate() + '"]').focus();
        };

        jQuery(picker.el).bind('click', function(e) {
            var isKeyboardClick = e.originalEvent.detail === 0;
            if (!isKeyboardClick) {
                return;
            }

            var $target = jQuery(e.target);
            var isPreviousMonth = $target.hasClass('pika-prev');
            picker._onMouseDown(e.originalEvent);

            if ($target.hasClass('pika-day')) {
                focusDay(getDayDate($target));
            }
            else {
                jQuery(picker.el).find(isPreviousMonth ? '.pika-prev' : '.pika-next').focus();
            }
        });

        jQuery(picker.el).bind('keydown', function(e) {
            var daysToMove = { ArrowLeft: -1, ArrowUp: -7, ArrowRight: 1, ArrowDown: 7 }[e.key];

            if (daysToMove && jQuery(e.target).hasClass('pika-day')) {
                e.preventDefault();
                var date = getDayDate(jQuery(e.target));
                date.setDate(date.getDate() + daysToMove);
                picker.gotoDate(date);
                focusDay(date);
            }
        });

        var select = function(when, start, end) {
            selection = { when: when, start: start, end: end };
            end = end || start;

            picker.setStartRange(start);
            picker.setEndRange(end);
            picker.draw();

            $quickLinks.find('.cnw_date-picker-quick-link')
                .removeClass('cnw_date-picker-quick-link--active')
                .filter(function() {
                    var $quickLink = jQuery(this);
                    return CultuurnetWidgets.isSameDay($quickLink.data('start'), start) && CultuurnetWidgets.isSameDay($quickLink.data('end'), end);
                })
                .addClass('cnw_date-picker-quick-link--active');

            var isCustomDate = when === 'custom_date';
            $datePicker.find('.cnw_date-picker-when').val(when);
            $datePicker.find('.cnw_date-picker-start').val(isCustomDate ? CultuurnetWidgets.formatDatePickerDate(start) : '');
            $datePicker.find('.cnw_date-picker-end').val(isCustomDate ? CultuurnetWidgets.formatDatePickerDate(end) : '');
            updateSummary();
        };

        var getWeeklySummary = function() {
            var days = $days.filter(':checked').map(function() {
                return jQuery(this).next().text();
            }).get();
            if (!days.length) {
                return '';
            }
            if (days.length === $days.length) {
                days = [$datePicker.find('.cnw_date-picker-days').data('every-day')];
            }

            var times = $times.map(function() {
                return this.value;
            }).get().filter(Boolean);

            return days.join(', ') + (times.length ? ' ' + times.join(' - ') : '');
        };

        var getDateSummary = function() {
            if (!selection.start) {
                return '';
            }

            var activeQuickLink = $quickLinks.find('.cnw_date-picker-quick-link--active').first().text();
            if (activeQuickLink) {
                return activeQuickLink;
            }

            var start = selection.start;
            var end = selection.end || start;

            return CultuurnetWidgets.formatDatePickerDate(start) + (end > start ? ' - ' + CultuurnetWidgets.formatDatePickerDate(end) : '');
        };

        var updateSummary = function() {
            $datePicker.find('.cnw_date-picker-summary').text(getWeeklySummary() || getDateSummary() || $trigger.data('placeholder'));
        };

        var applyMode = function(mode) {
            $modes.removeClass('cnw_date-picker-mode--active')
                .filter('[data-date-picker-mode="' + mode + '"]')
                .addClass('cnw_date-picker-mode--active');
            $datePicker.find('[data-date-picker-panel]').hide()
                .filter('[data-date-picker-panel="' + mode + '"]')
                .show();
        };

        var selectQuickLink = function() {
            var $quickLink = jQuery(this);

            if ($quickLink.hasClass('cnw_date-picker-quick-link--active')) {
                return select('', null, null);
            }

            picker.gotoDate($quickLink.data('start'));
            select($quickLink.data('when') || 'custom_date', $quickLink.data('start'), $quickLink.data('end'));
        };

        var toggleDropdown = function(visible) {
            $dropdown.toggle(visible);
            $trigger.attr('aria-expanded', visible);

            if (visible) {
                loadHolidays(new Date(today.getFullYear() + 1, 0, 1));
                picker.draw();
            }
        };

        $trigger.bind('click', function() {
            toggleDropdown(!$dropdown.is(':visible'));
        });

        $datePicker.find('.cnw_date-picker-confirm').bind('click', function() {
            toggleDropdown(false);
        });

        $dropdown.bind('keydown', function(e) {
            if (e.key === 'Escape') {
                toggleDropdown(false);
                $trigger.focus();
            }

            var isEnterInInput = e.key === 'Enter' && jQuery(e.target).is('input');
            if (isEnterInInput) {
                e.preventDefault();

                if (jQuery(e.target).is(':checkbox')) {
                    e.target.click();
                }
            }
        });

        jQuery(document).bind('click', function(e) {
            if (jQuery.contains(document.documentElement, e.target) && !jQuery(e.target).closest($datePicker).length) {
                toggleDropdown(false);
            }
        });

        $quickLinks.find('.cnw_date-picker-quick-link').bind('click', selectQuickLink).each(function() {
            var range = CultuurnetWidgets.datePickerQuickLinkRange(jQuery(this).data('when'), today);
            jQuery(this).data({ start: range[0], end: range[1] });
        });

        $holidaysToggle.bind('change', function() {
            picker.draw();
        });

        $modes.bind('click', function() {
            var $mode = jQuery(this);

            if ($mode.hasClass('cnw_date-picker-mode--active')) {
                return;
            }

            $days.prop('checked', false);
            $times.val('');
            select('', null, null);
            applyMode($mode.data('date-picker-mode'));
        });

        $days.add($times).bind('change', updateSummary);
        applyMode($days.is(':checked') ? 'weekly' : 'date');
        updateSummary();

        var params = CultuurnetWidgets.getCurrentParams(true);
        var getParam = function(key) {
            return params['search-form[' + $datePicker.data('widget-id') + '][' + key + ']'] || '';
        };

        var when = getParam('when');
        var start = CultuurnetWidgets.parseDatePickerDate(getParam('date-start'));

        if (when === 'custom_date' && start) {
            picker.gotoDate(start);
            select(when, start, CultuurnetWidgets.parseDatePickerDate(getParam('date-end')));
            loadHolidays(selection.end || start);
        }
        else {
            $quickLinks.find('[data-when="' + when + '"]').trigger('click');
        }
    };

    /**
     * Get the first and last day of a quick link.
     *
     * @param when
     * @param today
     */
    CultuurnetWidgets.datePickerQuickLinkRange = function(when, today) {
        var start = new Date(today);
        var end = new Date(today);

        if (when === 'tomorrow') {
            start.setDate(today.getDate() + 1);
            end.setDate(today.getDate() + 1);
        }
        else if (when === 'thisweekend') {
            var daysUntilSunday = (7 - today.getDay()) % 7;
            end.setDate(today.getDate() + daysUntilSunday);

            var saturday = new Date(end);
            saturday.setDate(end.getDate() - 1);
            start = saturday < today ? new Date(today) : saturday;
        }

        return [start, end];
    };

    /**
     * Get the public and Flemish school holidays from the holidays API response.
     *
     * @param data
     * @param language
     */
    CultuurnetWidgets.parseDatePickerHolidays = function(data, language) {
        return data.filter(function(holiday) {
            return !holiday.region || holiday.region === 'NL';
        }).map(function(holiday) {
            return {
                name: holiday.name[language] || holiday.name.nl,
                start: new Date(holiday.startDate + 'T00:00'),
                end: new Date(holiday.endDate + 'T00:00'),
                isSchoolHoliday: holiday.type === 'schoolHolidays'
            };
        });
    };

    /**
     * Get the Pikaday translations for the given language.
     *
     * @param language
     */
    CultuurnetWidgets.datePickerI18n = function(language) {
        var i18n = {
            previousMonth: '',
            nextMonth: '',
            months: [],
            weekdays: [],
            weekdaysShort: []
        };

        for (var month = 0; month < 12; month++) {
            i18n.months.push(new Date(2000, month, 1).toLocaleString(language, { month: 'long' }));
        }

        var sunday = new Date(2000, 0, 2);
        for (var weekday = 0; weekday < 7; weekday++) {
            var date = new Date(2000, 0, sunday.getDate() + weekday);
            i18n.weekdays.push(date.toLocaleString(language, { weekday: 'long' }));
            i18n.weekdaysShort.push(date.toLocaleString(language, { weekday: 'short' }));
        }

        return i18n;
    };

    /**
     * Check if two dates are on the same day.
     *
     * @param a
     * @param b
     */
    CultuurnetWidgets.isSameDay = function(a, b) {
        return !!a && !!b && a.toDateString() === b.toDateString();
    };

    /**
     * Format a date as dd/mm/yyyy.
     *
     * @param date
     */
    CultuurnetWidgets.formatDatePickerDate = function(date) {
        return date.toLocaleDateString('nl-BE', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    /**
     * Format a date as yyyy-mm-dd.
     *
     * @param date
     */
    CultuurnetWidgets.formatDatePickerIsoDate = function(date) {
        return CultuurnetWidgets.formatDatePickerDate(date).split('/').reverse().join('-');
    };

    /**
     * Parse a dd/mm/yyyy date.
     *
     * @param value
     */
    CultuurnetWidgets.parseDatePickerDate = function(value) {
        var parts = value.split('/');

        return parts.length === 3 ? new Date(parts[2], parts[1] - 1, parts[0]) : null;
    };

})(CultuurnetWidgets);
