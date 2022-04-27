(function (CultuurnetWidgets) {
// TODO check when multiple widgets on same page? Only load snowplow once?
var SNOWPLOW_JS_URL = 'https://unpkg.com/browse/@snowplow/javascript-tracker@3.1.6/dist/sp.js';

var WIDGET_PAGE_ID = Object.keys(CultuurnetWidgetsSettings)[0];
var WIDGET_SETTINGS = CultuurnetWidgetsSettings[WIDGET_PAGE_ID];

function initializeSnowPlow(p, l, o, w, i, n, g) {
  if (!p[i]) {
    p.GlobalSnowplowNamespace = p.GlobalSnowplowNamespace || [];
    p.GlobalSnowplowNamespace.push(i);
    p[i] = function () {
      (p[i].q = p[i].q || []).push(arguments);
    };
    p[i].q = p[i].q || [];
    n = l.createElement(o);
    g = l.getElementsByTagName(o)[0];
    n.async = 1;
    n.src = w;
    g.parentNode.insertBefore(n, g);
  }
};

initializeSnowPlow(window, document, "script", SNOWPLOW_JS_URL, "snowplow");

window.snowplow('newTracker', 'sp1', SNOWPLOW_JS_URL, { 
  appId: 'widgets ' + WIDGET_SETTINGS.consumerName
});

window.snowplow('trackPageView');


function trackClick() {
  const clickElements = document.querySelectorAll("[data-click-tracking-category]");

  clickElements.forEach(function(clickElement) {
    clickElement.addEventListener('click', function() {
      const category = clickElement.dataset.clickTrackingCategory;
      const label = clickElement.dataset.clickTrackingLabel;
      const action = clickElement.dataset.clickTrackingAction;
    })
  })
}

window.addEventListener('widget:searchResultsLoaded', (event) => {
  trackClick();
})

})(CultuurnetWidgets);
