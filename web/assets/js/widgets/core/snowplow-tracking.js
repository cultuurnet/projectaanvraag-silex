(function (CultuurnetWidgets) {
  const STARTTIME = new Date();
  // TODO check when multiple widgets on same page? Only load snowplow once?
  const SNOWPLOW_JS_URL =
    "https://cdn.jsdelivr.net/npm/@snowplow/javascript-tracker@3.1.6/dist/sp.min.js";

  const WIDGET_PAGE_ID = Object.keys(CultuurnetWidgetsSettings)[0];
  const WIDGET_SETTINGS = CultuurnetWidgetsSettings[WIDGET_PAGE_ID];

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const cdbid = urlParams.get("cdbid");

  const getEnvironment = () => {
    const apiUrl = WIDGET_SETTINGS.apiUrl;
    if (apiUrl.startsWith("https://projectaanvraag-api.uitdatabank.dev"))
      return "dev";
    if (apiUrl.startsWith("https://projectaanvraag-api-test.uitdatabank.be"))
      return "test";
    if (apiUrl.startsWith("https://projectaanvraag-api.uitdatabank.be"))
      return "prod";
  };

  const environment = getEnvironment();

  const getTimeSpentInSeconds = () => {
    const endTime = new Date();
    console.log("startTime", STARTTIME);
    console.log("endTime", endTime);
    return (endTime.getTime() - STARTTIME.getTime()) / 1000;
  };

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
  };

  const trackClicks = () => {
    const clickElements = document.querySelectorAll(
      "[data-click-tracking-category]"
    );

    clickElements.forEach((clickElement) => {
      clickElement.addEventListener("click", () => {
        const category = clickElement.dataset.clickTrackingCategory;
        const label = clickElement.dataset.clickTrackingLabel;
        const action = clickElement.dataset.clickTrackingAction;

        window.snowplow("trackSelfDescribingEvent", {
          event: {
            // TODO get schema for click
            schema: "iglu:be.uitinvlaanderen/button_click/jsonschema/1-0-0",
            data: {
              button_name: `${action}-${label}-${category}`,
            },
          },
        });
      });
    });
  };

  initializeSnowPlow(window, document, "script", SNOWPLOW_JS_URL, "snowplow");

  window.snowplow("newTracker", "widgets-tracker", "sp.uitinvlaanderen.be", {
    appId: "widgets",
    platform: "web",
    cookieDomain: null,
    cookieName: "sppubliq",
    sessionCookieTimeout: 3600,
    discoverRootDomain: true,
    eventMethod: "post",
    encodeBase64: true,
    respectDoNotTrack: false,
    userFingerprint: true,
    postPath: "/publiq/t",
    contexts: {
      webPage: true,
      performanceTiming: false,
      gaCookies: true,
      geolocation: false,
    },
  });

  // window.snowplow("addGlobalContexts", {
  //   schema: "iglu:be.uitinvlaanderen/widget_context/jsonschema/1-0-0",
  //   data: {
  //     widget_name: WIDGET_SETTINGS.consumerName,
  //     widget_page_id: WIDGET_SETTINGS.widgetPageId,
  //     environment,
  //     ...(cdbid && { cdbid }),
  //   },
  // });

  window.snowplow("trackPageView");

  window.snowplow("enableLinkClickTracking");

  window.addEventListener("widget:searchResultsLoaded", () => {
    trackClicks();
    trackViewedEventTeasers();
  });

  window.addEventListener("widget:eventDetailLoaded", () => {
    trackClicks();
  });

  window.addEventListener("beforeunload", (event) => {
    const timeSpent = getTimeSpentInSeconds();
    console.log("timeSpent", timeSpent);
    // TODO send viewedEventTeasers to snowplow
    // Use same event as timeSpent or seperate event?
  });

  let viewedEventTeasers = new Set();

  const observer = new window.IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        const readMoreButton = entry.target.getElementsByClassName(
          "cnw_btn__card-readmore"
        );
        if (readMoreButton[0]) {
          const uri = readMoreButton[0].href;
          const url = new URL(uri);
          const cdbid = url.searchParams.get("cdbid");
          viewedEventTeasers.add(cdbid);
        }
        return;
      }
    },
    {
      root: null,
      threshold: 0.1, // set offset 0.1 means trigger if atleast 10% of element in viewport
    }
  );

  const trackViewedEventTeasers = () => {
    const eventTeaserBlocks = document.getElementsByClassName(
      "cnw_searchresult--block"
    );
    Object.values(eventTeaserBlocks).forEach(observer.observe);
  };
})(CultuurnetWidgets);
