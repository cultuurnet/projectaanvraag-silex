
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets) {

    'use strict';

    CultuurnetWidgets.currentParams = undefined;

    /**
     * Bootstrap the widgets.
     */
    CultuurnetWidgets.prepareBootstrap = function(widgetPageId) {

        // If jquery exists on the site, attach behaviors.
        if (((window.jQuery && parseFloat(jQuery.fn.jquery.substring(0, 3))>1.5) || window.CultuurnetWidgetsSettings[widgetPageId].jquery)) {
            window.addEventListener("load", function(){
                CultuurnetWidgets.bootstrap(widgetPageId);
            }, false);
        }
        // If jQuery does not exists, load it and attach behaviors.
        else {
            var script = document.createElement('script');
            document.head.appendChild(script);
            script.type = 'text/javascript';
            script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
            window.addEventListener("load", function(){
                CultuurnetWidgets.bootstrap(widgetPageId);
            }, false);
        }
        CultuurnetWidgets.initTagManager(widgetPageId);
    };

    /**
     * Bootstrap the widgets page.
     */
    CultuurnetWidgets.bootstrap = function(widgetPageId) {

        // No page id => nothing to do.
        if (!widgetPageId) {
            return;
        }

        var $wrapper = jQuery('#cultuurnet-widgets-' + widgetPageId);
        if ($wrapper.length === 0) {
            return;
        }

        if(CultuurnetWidgetsSettings[widgetPageId].mobile) {
          $wrapper.addClass('xs');
        }

        // If a cdbid is given in url, and a detail page is in settings. Load detail.
        var params = CultuurnetWidgets.getCurrentParams();
        var loadDetail = params['cdbid'] && CultuurnetWidgetsSettings[widgetPageId].detailPage && CultuurnetWidgetsSettings[widgetPageId].detailPageRowId != undefined;

        $wrapper.html('');
        for (var i = 0; i < CultuurnetWidgetsSettings[widgetPageId].widgetPageRows.length; i++) {
            if (loadDetail && i == CultuurnetWidgetsSettings[widgetPageId].detailPageRowId) {
                $wrapper.append(CultuurnetWidgetsSettings[widgetPageId].detailPage);
            }
            else {
                $wrapper.append(CultuurnetWidgetsSettings[widgetPageId].widgetPageRows[i]);
            }
        }

        CultuurnetWidgets.attachBehaviors($wrapper, widgetPageId);
    };

    /**
     * Attaches all registered behaviors to a page element.
     *
     * @param {HTMLDocument|HTMLElement} [context=document]
     * @param {String} widgetPageId
     *   An element to attach behaviors to.*
     */
    CultuurnetWidgets.attachBehaviors = function (context, widgetPageId) {

        context = context || document;
        var behaviors = CultuurnetWidgets.behaviors;
        // Execute all of them.
        for (var i in behaviors) {
            if (behaviors.hasOwnProperty(i) && typeof behaviors[i].attach === 'function') {
                // Don't stop the execution of behaviors in case of an error.
                try {
                    behaviors[i].attach(context, widgetPageId);
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
        var styleElement = document.createElement("link");
        styleElement.setAttribute("rel", "stylesheet");
        styleElement.setAttribute("type", "text/css");
        styleElement.setAttribute("href", css_file);
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
        if (CultuurnetWidgetsSettings[widgetPageId].widgetMapping && CultuurnetWidgetsSettings[widgetPageId].widgetMapping.hasOwnProperty(widgetId)) {
            return CultuurnetWidgets.apiRequest(CultuurnetWidgetsSettings[widgetPageId].apiUrl + '/render/' + CultuurnetWidgetsSettings[widgetPageId].widgetMapping[widgetId] + '/' + widgetId);
        }
        else {
            deferred.reject('The given widget id was not found');
        }

        return deferred;
    };

    /**
     * Render a given search results widget + all related facets.
     */
    CultuurnetWidgets.renderSearchResults = function(widgetId, widgetPageId) {

        var deferred = jQuery.Deferred();

        // Only render the widget if it's a known id.
        if (CultuurnetWidgetsSettings[widgetPageId].widgetMapping && CultuurnetWidgetsSettings[widgetPageId].widgetMapping.hasOwnProperty(widgetId)) {
            return CultuurnetWidgets.apiRequest(CultuurnetWidgetsSettings[widgetPageId].apiUrl + '/render/' + CultuurnetWidgetsSettings[widgetPageId].widgetMapping[widgetId] + '/' + widgetId + '/search-results-with-facets');
        }
        else {
            deferred.reject('The given widget id was not found');
        }

        return deferred;
    };

    /**
     * Render a given detail page for a search results widget.
     */
    CultuurnetWidgets.renderDetailPage = function(widgetId, widgetPageId) {

        var deferred = jQuery.Deferred();

        // Only render the widget if it's a known id.
        if (CultuurnetWidgetsSettings[widgetPageId].widgetMapping && CultuurnetWidgetsSettings[widgetPageId].widgetMapping.hasOwnProperty(widgetId)) {
            return CultuurnetWidgets.apiRequest(CultuurnetWidgetsSettings[widgetPageId].apiUrl + '/render/' + CultuurnetWidgetsSettings[widgetPageId].widgetMapping[widgetId] + '/' + widgetId + '/detail');
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
    CultuurnetWidgets.redirectWithNewParams = function(paramsToAdd, openInNewWindow, location) {

        // Check for existing query parameters.
        var queryString = window.location.search;
        var newParams = {};
        if (queryString) {
            // Convert existing query string to an object.
            newParams = JSON.parse('{"' + decodeURI(queryString.substr(1).replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');
        }

        // Add every param to the params.
        for (var param in paramsToAdd) {
            if (typeof newParams[param] !== 'undefined') {
                delete newParams[param];
            }

            // Only add params that are not marked as 'to delete'.
            if (paramsToAdd[param] !== 'delete-param') {
                newParams[param] = paramsToAdd[param];
            }
        }

        if (!location) {
            location = window.location.pathname;
        }

        if (openInNewWindow) {
            window.open(location + '?' + CultuurnetWidgets.buildQueryUrl(newParams));
        }
        else {
            window.location.href = location + '?' + CultuurnetWidgets.buildQueryUrl(newParams);
        }
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
            console.log(currentParams);
            for (var index in paramsToDelete) {
              console.log(paramsToDelete[index]);
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

    CultuurnetWidgets.addLoadEvent = function(func) {
      var oldonload = window.onload;
      if (typeof window.onload != 'function') {
        window.onload = func;
      } else {
        window.onload = function() {
          if (oldonload) {
            oldonload();
          }
          func();
        }
      }
    }

})(CultuurnetWidgets);
