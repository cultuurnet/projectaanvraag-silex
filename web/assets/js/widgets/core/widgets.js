
alert('widgets loaded');

window.CultuurnetWidgets = window.CultuurnetWidgets || { behaviors: {} };

(function (CultuurnetWidgets) {

    'use strict';

    /**
     * Adds the provided inline CSS to the head
     * @param string css CSS script to add inline
     * @return null
     */
    CultuurnetWidgets.addStyle = function (css) {

        alert('loaded css' + css);

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

})(CultuurnetWidgets);