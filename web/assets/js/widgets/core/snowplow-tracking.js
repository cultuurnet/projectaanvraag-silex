(function (CultuurnetWidgets) {
  CultuurnetWidgets.initSnowplow = () => {
    const STARTTIME = new Date();
    const SNOWPLOW_JS_URL =
      "https://cdn.jsdelivr.net/npm/@snowplow/javascript-tracker@3.1.6/dist/sp.min.js";

    const WIDGET_PAGE_ID = Object.keys(CultuurnetWidgetsSettings)[0];
    const WIDGET_SETTINGS = CultuurnetWidgetsSettings[WIDGET_PAGE_ID];

    const queryString = window.location.search;
    const decodedQueryString = decodeURI(queryString);
    const urlParams = new URLSearchParams(queryString);
    const cdbid = urlParams.get("cdbid");
    const pageType = cdbid ? "event_page" : "search_page";

    const usedSearchTerms = queryString.includes("search-form");
    const usedSearchFacets = queryString.includes("facets");

    // Used to get search terms from the url
    // E.g. ?search-form[bf058f96-7493-1aa4-be08-1ef14047d70b][when]=tomorrow
    const getSearchTerm = (termType) => {
      const searchTerm =
        usedSearchTerms && decodedQueryString.includes(`[${termType}]=`)
          ? decodedQueryString.split(`[${termType}]=`)[1]
          : "";
      return searchTerm.includes("&") ? searchTerm.split("&")[0] : searchTerm;
    };

    const getSearchFacet = (facetType) => {
      const searchFacetQueryPart =
        usedSearchFacets && decodedQueryString.includes(`[${facetType}][`)
          ? decodedQueryString.split(`[${facetType}][`)[1]
          : "";
      if (!searchFacetQueryPart) {
        return "";
      }
      const searchFacet = searchFacetQueryPart.split("]=")[1];
      return searchFacet.includes("&")
        ? searchFacet.split("&")[0]
        : searchFacet;
    };

    const searchTermWhat = getSearchTerm("what");
    const searchTermWhere = getSearchTerm("where");
    const searchTermWhen = getSearchTerm("when");

    const searchFacetWhat = getSearchFacet("what");
    const searchFacetWhere = getSearchFacet("where");
    const searchFacetWhen = getSearchFacet("when");

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

    const stringToKebabCase = (value) => {
      return value.split(" ").join("-").toLowerCase();
    };

    initializeSnowPlow(window, document, "script", SNOWPLOW_JS_URL, "snowplow");

    window.snowplow(
      "newTracker",
      "widgets-tracker",
      "sneeuwploeg.uitdatabank.be",
      {
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
      }
    );

    const GLOBAL_WIDGET_CONTEXT = {
      schema: "iglu:be.widgets/widget_context/jsonschema/1-0-2",
      data: {
        name: WIDGET_SETTINGS.consumerName,
        title: WIDGET_SETTINGS.widgetPageTitle,
        language: WIDGET_SETTINGS.language ?? "",
        page_id: WIDGET_SETTINGS.widgetPageId,
        page_type: pageType,
        search_terms: {
          what: searchTermWhat,
          when: searchTermWhen,
          where: searchTermWhere,
        },
        search_facets: {
          what: searchFacetWhat,
          where: searchFacetWhere,
          when: searchFacetWhen,
        },
        cdbid: cdbid ?? "",
      },
    };

    const GLOBAL_ENVIRONMENT_CONTEXT = {
      schema: "iglu:be.general/app_env/jsonschema/1-0-0",
      data: {
        environment,
      },
    };

    window.snowplow("addGlobalContexts", [
      GLOBAL_WIDGET_CONTEXT,
      GLOBAL_ENVIRONMENT_CONTEXT,
    ]);

    window.snowplow("trackPageView");

    window.snowplow("enableLinkClickTracking");

    const observerCallback = (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        const readMoreButtons = entry.target.getElementsByClassName(
          "cnw_btn__card-readmore"
        );

        if (!readMoreButtons[0]) return;

        const uri = readMoreButtons[0].href;
        const url = new URL(uri);
        const cdbidOfEventTeaser = url.searchParams.get("cdbid");
        viewedEventTeasers.add(cdbidOfEventTeaser);
      });
    };

    const observerOptions = {
      root: null,
      threshold: 0.1, // set offset 0.1 means trigger if atleast 10% of element in viewport
    };

    const observer = new IntersectionObserver(
      observerCallback,
      observerOptions
    );

    const trackViewedEventTeasers = () => {
      const eventTeasersBlocks = document.querySelectorAll(
        ".cnw_searchresult--block"
      );
      Array.from(eventTeasersBlocks).forEach((target) =>
        observer.observe(target)
      );
    };

    const trackButtonClicks = () => {
      const clickElements = document.querySelectorAll(
        "[data-click-tracking-category]"
      );

      clickElements.forEach((clickElement) => {
        clickElement.addEventListener("click", () => {
          const category = clickElement.dataset.clickTrackingCategory;
          const label = clickElement.dataset.clickTrackingLabel;
          const action = clickElement.dataset.clickTrackingAction;

          const buttonName = [category, label, action]
            .filter((item) => typeof item !== "undefined")
            .map((item) => stringToKebabCase(item))
            .join("-");

          window.snowplow("trackSelfDescribingEvent", {
            event: {
              schema: "iglu:be.general/button_click/jsonschema/1-0-0",
              data: {
                button_name: buttonName ?? "",
              },
            },
          });
        });
      });
    };

    window.addEventListener("widget:searchResultsLoaded", () => {
      trackButtonClicks();
      trackViewedEventTeasers();
    });

    window.addEventListener("widget:tipResultsLoaded", () => {
      trackButtonClicks();
      trackViewedEventTeasers();
    });

    window.addEventListener("widget:eventDetailLoaded", () => {
      trackButtonClicks();
    });

    window.addEventListener("beforeunload", () => {
      const timeSpent = getTimeSpentInSeconds();
      const activeSeconds = Math.round(timeSpent);

      window.snowplow("trackSelfDescribingEvent", {
        event: {
          schema: "iglu:be.general/page_unload/jsonschema/1-0-0",
          data: {
            active_seconds: activeSeconds,
          },
        },
      });

      window.snowplow("trackSelfDescribingEvent", {
        event: {
          schema: "iglu:be.widgets/impressions/jsonschema/1-0-0",
          data: {
            event_impressions: [...viewedEventTeasers].map((id) => ({
              event_id: id,
            })),
          },
        },
      });
    });
  };
})(CultuurnetWidgets);
