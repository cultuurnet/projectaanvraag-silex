
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets) {

    'use strict';

    CultuurnetWidgets.currentParams = undefined;

    /**
     * Bootstrap the widgets.
     */
    CultuurnetWidgets.prepareBootstrap = function() {

        // If jquery exists on the site, attach behaviors.
        if (window.jQuery) {
            console.log('bootstrap');
            CultuurnetWidgets.bootstrap();
        }
        // If jQuery does not exists, load it and attach behaviors.
        else {
            console.log('bootstrap');
            var script = document.createElement('script');
            document.head.appendChild(script);
            script.type = 'text/javascript';
            script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
            script.onload = CultuurnetWidgets.bootstrap;
        }

        CultuurnetWidgets.initTagManager();
    };

    /**
     * Bootstrap the widgets page.
     */
    CultuurnetWidgets.bootstrap = function() {

        // No page id => nothing to do.
        if (!CultuurnetWidgetsSettings.widgetPageId) {
            return;
        }

        var $wrapper = jQuery('#cultuurnet-widgets-' + CultuurnetWidgetsSettings.widgetPageId);
        if ($wrapper.length === 0) {
            return;
        }

        // If a cdbid is given in url, and a detail page is in settings. Load detail.
        var params = CultuurnetWidgets.getCurrentParams();
        var loadDetail = params['cdbid'] && CultuurnetWidgetsSettings.detailPage && CultuurnetWidgetsSettings.detailPageRowId;

        $wrapper.html('');
        for (var i in CultuurnetWidgetsSettings.widgetPageRows) {
            if (loadDetail && i == CultuurnetWidgetsSettings.detailPageRowId) {
                $wrapper.append(CultuurnetWidgetsSettings.detailPage);
            }
            else {
                $wrapper.append(CultuurnetWidgetsSettings.widgetPageRows[i]);
            }
        }

        CultuurnetWidgets.attachBehaviors($wrapper);
    };

    /**
     * Attaches all registered behaviors to a page element.
     *
     * @param {HTMLDocument|HTMLElement} [context=document]
     *   An element to attach behaviors to.*
     */
    CultuurnetWidgets.attachBehaviors = function (context) {

        context = context || document;
        var behaviors = CultuurnetWidgets.behaviors;
        // Execute all of them.
        for (var i in behaviors) {
            if (behaviors.hasOwnProperty(i) && typeof behaviors[i].attach === 'function') {
                // Don't stop the execution of behaviors in case of an error.
                try {
                    behaviors[i].attach(context);
                }
                catch (e) {
                    CultuurnetWidgets.log(e);
                }
            }
        }
    };

    /**
     * Detaches registered behaviors from a page element.
     */
    CultuurnetWidgets.detachBehaviors = function (context, settings, trigger) {
        context = context || document;
        trigger = trigger || 'unload';
        var behaviors = CultuurnetWidgets.behaviors;
        // Execute all of them.
        for (var i in behaviors) {
            if (behaviors.hasOwnProperty(i) && typeof behaviors[i].detach === 'function') {
                // Don't stop the execution of behaviors in case of an error.
                try {
                    behaviors[i].detach(context, trigger);
                }
                catch (e) {
                    CultuurnetWidgets.log(e);
                }
            }
        }
    };

    /**
     * Adds the provided inline CSS to the head
     * @param string css CSS script to add inline
     * @return null
     */
    CultuurnetWidgets.addStyle = function (css) {

        var head = document.getElementsByTagName('head')[0];
        var styleElement = document.createElement("style");
        styleElement.setAttribute("type", "text/css");
        if (styleElement.styleSheet) {
            styleElement.styleSheet.cssText = css;
        } else {
            var styleTextElement = document.createTextNode(css);
            styleElement.appendChild(styleTextElement);
        }
        head.appendChild(styleElement);
    };

    /**
     * Adds the provided external CSS to the head
     * @param string css_file Url to the css file to load.
     * @return null
     */
    CultuurnetWidgets.addExternalStyle = function (css_file) {

        var head = document.getElementsByTagName('head')[0];
        var styleElement = document.createElement("style");
        styleElement.setAttribute("type", "text/css");
        styleElement.setAttribute("src", css_file);
        head.appendChild(styleElement);
    };

    /**
     * Log messages to the console if a console exists.
     * @param message
     */
    CultuurnetWidgets.log = function(message) {
        if (typeof console != 'undefined' && typeof console.log != 'undefined') {
            console.log(message);
        }
    };

    /**
     * Calls the widget server with the provided url
     *
     * @param string url Url to invoke
     * @param params callback Callback on success
     * @return jqXHR
     */
    CultuurnetWidgets.apiRequest = function(url, params) {
        params = params || {};

        // Add current querystring to the URL.
        params.data = window.location.search.substring(1);

        if (typeof params.dataType == 'undefined') {
            params.dataType = 'jsonp';
        }

        if (params.dataType == 'jsonp') {
            params.crossDomain = true;
        }

        return jQuery.ajax(url, params);
    };

    /**
     * Render a given widget.
     */
    CultuurnetWidgets.renderWidget = function(widgetId) {

        var deferred = jQuery.Deferred();

        // Only render the widget if it's a known id.
        if (CultuurnetWidgetsSettings.widgetMapping && CultuurnetWidgetsSettings.widgetMapping.hasOwnProperty(widgetId)) {
            return CultuurnetWidgets.apiRequest(CultuurnetWidgetsSettings.apiUrl + '/render/' + CultuurnetWidgetsSettings.widgetMapping[widgetId] + '/' + widgetId);
        }
        else {
            deferred.reject('The given widget id was not found');
        }

        return deferred;
    };

    /**
     * Render a given search results widget + all related facets.
     */
    CultuurnetWidgets.renderSearchResults = function(widgetId) {

        var deferred = jQuery.Deferred();

        // Only render the widget if it's a known id.
        if (CultuurnetWidgetsSettings.widgetMapping && CultuurnetWidgetsSettings.widgetMapping.hasOwnProperty(widgetId)) {
            return CultuurnetWidgets.apiRequest(CultuurnetWidgetsSettings.apiUrl + '/render/' + CultuurnetWidgetsSettings.widgetMapping[widgetId] + '/' + widgetId + '/search-results-with-facets');
        }
        else {
            deferred.reject('The given widget id was not found');
        }

        return deferred;
    };

    /**
     * Render a given detail page for a search results widget.
     */
    CultuurnetWidgets.renderDetailPage = function(widgetId) {

        var deferred = jQuery.Deferred();

        // Only render the widget if it's a known id.
        if (CultuurnetWidgetsSettings.widgetMapping && CultuurnetWidgetsSettings.widgetMapping.hasOwnProperty(widgetId)) {
            return CultuurnetWidgets.apiRequest(CultuurnetWidgetsSettings.apiUrl + '/render/' + CultuurnetWidgetsSettings.widgetMapping[widgetId] + '/' + widgetId + '/detail');
        }
        else {
            deferred.reject('The given widget id was not found');
        }

        return deferred;
    };

    /**
     * Get the current query params.
     */
    CultuurnetWidgets.getCurrentParams = function() {

        if (CultuurnetWidgets.currentParams === undefined) {

            CultuurnetWidgets.currentParams = {};

            var queryString = decodeURI(window.location.search.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\""));
            if (queryString) {
                CultuurnetWidgets.currentParams = JSON.parse('{"' + queryString + '"}');
            }
        }

        return CultuurnetWidgets.currentParams;
    };

    /**
     * Perform a redirect with new parameters.
     */
    CultuurnetWidgets.redirectWithNewParams = function(paramsToAdd) {

        // Check for existing query parameters.
        var queryString = window.location.search;
        var newParams = [];
        if (queryString) {
            // Convert existing query string to an object.
            var newParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');

            // Add every param to the params.
            for (var param in paramsToAdd) {
                if (typeof newParams[param] !== 'undefined') {
                    delete newParams[param];
                }
                newParams[param] = paramsToAdd[param];
            }
        }
        else {
            newParams = paramsToAdd;
        }

        window.location.href = window.location.pathname + '?' + CultuurnetWidgets.buildQueryUrl(newParams);
    };

    /**
     * Perform a redirect without the given parameters.
     * @param paramsToDelete
     */
    CultuurnetWidgets.redirectAndDeleteParams = function(paramsToDelete) {

        // Check for existing query parameters.
        var queryString = window.location.search;
        if (queryString) {

            // Convert existing query string to an object.
            var currentParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');

            for (var index in paramsToDelete) {
                // Delete corresponding parameter from URL.
                if (typeof currentParams[paramsToDelete[index]] !== 'undefined') {
                    delete currentParams[paramsToDelete[index]];
                }
            }

            window.location.href = window.location.pathname + '?' + CultuurnetWidgets.buildQueryUrl(currentParams);

        }
        else {
            window.location.href = window.location.pathname;
        }
    };

    /**
     * Build a query string from updated params.
     *
     * @param currentParams
     * @returns {string}
     */
    CultuurnetWidgets.buildQueryUrl = function(currentParams) {
        var newParams = [];
        for (var key in currentParams) {
            newParams.push(key + '=' + currentParams[key]);
        }
        return newParams.join('&');
    }

})(CultuurnetWidgets);