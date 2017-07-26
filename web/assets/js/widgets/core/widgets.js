
window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets) {

    'use strict';

    /**
     * Bootstrap the widgets.
     */
    CultuurnetWidgets.prepareBootstrap = function() {

        // If jquery exists on the site, attach behaviors.
        if (window.jQuery) {
            CultuurnetWidgets.attachBehaviors();
        }
        // If jQuery does not exists, load it and attach beviors.
        else {
            var script = document.createElement('script');
            document.head.appendChild(script);
            script.type = 'text/javascript';
            script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
            script.onload = CultuurnetWidgets.attachBehaviors;
        }
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
     * Log messages to the console if a console exists.
     * @param message
     */
    CultuurnetWidgets.log = function(message) {
        if (typeof console != 'undefined' && typeof console.log != 'undefined') {
            console.log(message);
        }
    };

})(CultuurnetWidgets);