(function (CultuurnetWidgets) {
  const STARTTIME = new Date();
  // TODO check when multiple widgets on same page? Only load snowplow once?
  const SNOWPLOW_JS_URL =
    "https://cdn.jsdelivr.net/npm/@snowplow/javascript-tracker@3.1.6/dist/sp.min.js";

  const WIDGET_PAGE_ID = Object.keys(CultuurnetWidgetsSettings)[0];
  const WIDGET_SETTINGS = CultuurnetWidgetsSettings[WIDGET_PAGE_ID];

  console.log({WIDGET_PAGE_ID});
  console.log({WIDGET_SETTINGS});

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const cdbid = urlParams.get("cdbid");

  const viewedEventTeasers = new Set();

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

  // rename to trackButtonClicks
  const trackButtonClicks = () => {
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
            schema: "iglu:be.general/button_click/jsonschema/1-0-0",
            data: {
              button_name: `${action}-${label}-${category}`,
            },
          },
        });
      });
    });
  };

  initializeSnowPlow(window, document, "script", SNOWPLOW_JS_URL, "snowplow");

  // TODO check with Hanne to change sp.uitinvlaanderen to sp.projectaanvraag-api.be
  window.snowplow("newTracker", "widgets-tracker", "sp.uitinvlaanderen.be", {
    appId: "widgets",
    platform: "web",
    cookieDomain: null,
    cookieName: "sppubliq",
    sessionCookieTimeout: 3600,
    discoverRootDomain: true,
    eventMethod: "post",
    encodeBase64: true, // TODO check if encoding can always be enabled. 
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

  window.snowplow("addGlobalContexts", {
    schema: "iglu:be.uitinvlaanderen/widget_context/jsonschema/1-0-0",
    data: {
      name: WIDGET_SETTINGS.consumerName,
      page_id: WIDGET_SETTINGS.widgetPageId,
    },
  });
  
  window.snowplow("addGlobalContexts", {
    schema: "iglu:be.general/app_env/jsonschema/1-0-0",
    data: {
      environment,
    },
  });

  window.snowplow("trackPageView");

  window.snowplow("enableLinkClickTracking");

  window.addEventListener("widget:searchResultsLoaded", () => {
    trackButtonClicks();
    trackViewedEventTeasers();
  });

  window.addEventListener("widget:eventDetailLoaded", () => {
    trackButtonClicks();
  });

  window.addEventListener("beforeunload", (event) => {
    const timeSpent = getTimeSpentInSeconds();

    window.snowplow("trackSelfDescribingEvent", {
      event: {
        schema: "iglu:be.general/page_unload/jsonschema/1-0-0",
        data: {
          active_seconds: timeSpent,
        },
      },
    });
    console.log("timeSpent", timeSpent);
    console.log(viewedEventTeasers);
    // TODO send viewedEventTeasers to snowplow
    // Use same event as timeSpent or seperate event?
  });


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
    Object.values(eventTeaserBlocks).forEach((eventTeaserBlock) => observer.observe(eventTeaserBlock));
  };
})(CultuurnetWidgets);
