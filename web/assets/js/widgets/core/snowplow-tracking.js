(function (CultuurnetWidgets) {
// TODO check when multiple widgets on same page? Only load snowplow once?
const SNOWPLOW_JS_URL = 'https://unpkg.com/browse/@snowplow/javascript-tracker@3.1.6/dist/sp.js';

const WIDGET_PAGE_ID = Object.keys(CultuurnetWidgetsSettings)[0];
const WIDGET_SETTINGS = CultuurnetWidgetsSettings[WIDGET_PAGE_ID];

const initializeSnowPlow = (p, l, o, w, i, n, g) => {
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
}


const trackClicks = () => {
  const clickElements = document.querySelectorAll("[data-click-tracking-category]");

  clickElements.forEach((clickElement) => {
    clickElement.addEventListener('click', () => {
      const category = clickElement.dataset.clickTrackingCategory;
      const label = clickElement.dataset.clickTrackingLabel;
      const action = clickElement.dataset.clickTrackingAction;

      window.snowplow('trackSelfDescribingEvent', {
        event: {
          // TODO get schema for click
          schema: 'iglu:be.uitinvlaanderen/button_click/jsonschema/1-0-0', 
          data: {
            button_name: `${action}-${label}-${category}`
          }
        }
      });
      
    })
  })
}

initializeSnowPlow(window, document, "script", SNOWPLOW_JS_URL, "snowplow");

window.snowplow('newTracker', 'widgets-tracker', SNOWPLOW_JS_URL, { 
  appId: 'widgets',
  platform: "web",
	cookieDomain: null,
	cookieName: 'sppubliq',
	sessionCookieTimeout: 3600,
	discoverRootDomain: true,
	eventMethod: "post",
  encodeBase64: true,
	respectDoNotTrack: false,
	userFingerprint: true,
  postPath: '/publiq/t',
	contexts: {
		webPage: true,
		performanceTiming: false,
		gaCookies: true,
		geolocation: false
	}
});

window.snowplow("addGlobalContexts", {
  schema: "iglu:be.uitinvlaanderen/widget_context/jsonschema/1-0-0",
  data: {
    widget_name: WIDGET_SETTINGS.consumerName,
  },
});

window.snowplow('trackPageView');

window.snowplow('enableLinkClickTracking');

window.addEventListener('widget:searchResultsLoaded', () => {
  trackClicks();
})

})(CultuurnetWidgets);
