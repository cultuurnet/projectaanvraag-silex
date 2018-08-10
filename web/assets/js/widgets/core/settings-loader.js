
(function () {

    'use strict';

    /**
     * Load the settings element for cultuurnet widgets.
     *
     * @param settingsJson
     *   JSON string containing the settings.
     */
    CultuurnetWidgets.loadSettings = function(settingsJson) {
        if(!window.CultuurnetWidgetsSettings) {
          window.CultuurnetWidgetsSettings = [];
        }
        if (settingsJson !== null) {
            window.CultuurnetWidgetsSettings[settingsJson.widgetPageId] = settingsJson;
        }
    }

 })();
