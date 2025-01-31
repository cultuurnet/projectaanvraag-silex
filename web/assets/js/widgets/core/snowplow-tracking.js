(function (CultuurnetWidgets) {
  CultuurnetWidgets.initSnowplow = () => {
    const STARTTIME = new Date();
    const SNOWPLOW_JS_URL =
      "https://cdn.jsdelivr.net/npm/@snowplow/javascript-tracker@3.1.6/dist/sp.min.js";

    const SNOWPLOW_TRACKER_NAME = "widgets-tracker";

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

    // Schema configuration by environment and event type
    // Schema versions can be different per environment
    const SCHEMA_CONFIG = {
      dev: {
        widget_context: {
          name: "be.widgets/widget_context",
          version: "2-0-0"
        },
        button_click: {
          name: "be.general/button_click",
          version: "1-0-0"
        },
        page_unload: {
          name: "be.general/page_unload",
          version: "1-0-0"
        },
        impressions: {
          name: "be.widgets/impressions",
          version: "1-0-0"
        },
        app_env: {
          name: "be.general/app_env",
          version: "1-0-0"
        }
      },
      prod: {
        widget_context: {
          name: "be.widgets/widget_context",
          version: "1-0-2"
        },
        button_click: {
          name: "be.general/button_click",
          version: "1-0-0"
        },
        page_unload: {
          name: "be.general/page_unload",
          version: "1-0-0"
        },
        impressions: {
          name: "be.widgets/impressions",
          version: "1-0-0"
        },
        app_env: {
          name: "be.general/app_env",
          version: "1-0-0"
        }
      }
    };

    const getEnvironment = () => {
      const apiUrl = WIDGET_SETTINGS.apiUrl;
      if (apiUrl.startsWith("https://projectaanvraag-api.uitdatabank.dev"))
        return {
          name: "dev",
          collector: "sneeuwploeg-dev.uitdatabank.be",
          snowplowBackendEnvironment: "dev"  // Maps to dev Snowplow backend
        };
      if (apiUrl.startsWith("https://projectaanvraag-api-test.uitdatabank.be"))
        return {
          name: "test",
          collector: "sneeuwploeg-dev.uitdatabank.be", // Use dev collector
          snowplowBackendEnvironment: "dev"  // Test frontend maps to dev Snowplow backend
        };
      if (apiUrl.startsWith("https://projectaanvraag-api.uitdatabank.be"))
        return {
          name: "prod",
          collector: "sneeuwploeg-prd.uitdatabank.be", // Single production collector
          snowplowBackendEnvironment: "prod"  // Maps to prod Snowplow backend
        };
    };

    const environmentConfig = getEnvironment();
    const environment = environmentConfig.name;
    const snowplowBackendEnvironment = environmentConfig.snowplowBackendEnvironment;

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

    initializeSnowPlow(window, document, "script", SNOWPLOW_JS_URL, "widgetSnowplow");

    // Common tracker configuration
    const getTrackerConfig = () => ({
      appId: "widgets",
      platform: "web",
      cookieDomain: null,
      cookieName: "sppubliq",  // Same cookie name for all trackers
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
      }
    });

    // Initialize tracker(s) based on environment
    const initializeTrackers = () => {
      window.widgetSnowplow(
        "newTracker",
        `widgets-tracker-${environment}`, // Use specific environment name in tracker
        environmentConfig.collector,
        getTrackerConfig()
      );
    };

    initializeTrackers();

    // Event data collectors by environment
    // These functions gather all required data for each event type in each environment
    // Input data for each event might be different per environment (depends on schema version)
    const EVENT_DATA_COLLECTORS = {
      dev: {
        page_unload: () => {
          const timeSpent = getTimeSpentInSeconds();
          return {
            activeSeconds: Math.round(timeSpent),
          };
        },
        button_click: (category, label, action) => {
          const buttonName = [category, label, action]
            .filter((item) => typeof item !== "undefined")
            .map((item) => stringToKebabCase(item))
            .join("-");
          
          return {
            buttonName,
          };
        },
        widget_context: () => ({
          consumerName: WIDGET_SETTINGS.consumerName,
          widgetPageTitle: WIDGET_SETTINGS.widgetPageTitle,
          language: WIDGET_SETTINGS.language,
          widgetPageId: WIDGET_SETTINGS.widgetPageId,
          pageType,
          searchTerms: {
            what: searchTermWhat,
            when: searchTermWhen,
            where: searchTermWhere,
          },
          searchFacets: {
            what: searchFacetWhat,
            where: searchFacetWhere,
            when: searchFacetWhen,
          },
          cdbid,
        }),
        impressions: () => ({
          eventImpressions: [...viewedEventTeasers].map((id) => ({
            event_id: id,
          }))
        }),
        app_env: () => ({
          environment,
        })
      },
      test: {
        // Test environment uses same collectors as dev
        ...EVENT_DATA_COLLECTORS.dev
      },
      prod: {
        page_unload: () => {
          const timeSpent = getTimeSpentInSeconds();
          return {
            activeSeconds: Math.round(timeSpent)
          };
        },
        button_click: (category, label, action) => {
          const buttonName = [category, label, action]
            .filter((item) => typeof item !== "undefined")
            .map((item) => stringToKebabCase(item))
            .join("-");
          buttonName = buttonName ?? "";
          return {
            buttonName
          };
        },
        widget_context: () => ({
          consumerName: WIDGET_SETTINGS.consumerName,
          widgetPageTitle: WIDGET_SETTINGS.widgetPageTitle,
          language: WIDGET_SETTINGS.language,
          widgetPageId: WIDGET_SETTINGS.widgetPageId,
          pageType,
          searchTerms: {
            what: searchTermWhat,
            when: searchTermWhen,
            where: searchTermWhere,
          },
          searchFacets: {
            what: searchFacetWhat,
            where: searchFacetWhere,
            when: searchFacetWhen,
          },
          cdbid
        }),
        impressions: () => ({
          eventImpressions: [...viewedEventTeasers].map((id) => ({
            event_id: id
          }))
        }),
        app_env: () => ({
          environment
        })
      }
    };

    // Event builders that format the collected data according to each schema
    const EVENT_BUILDERS = {
      dev: {
        page_unload: (data) => ({
          schema: getSchemaUrl('page_unload', 'dev'),
          data: {
            active_seconds: data.activeSeconds,
            user: data.user
          }
        }),
        button_click: (data) => ({
          schema: getSchemaUrl('button_click', 'dev'),
          data: {
            button_name: data.buttonName,
            clicked_at: data.timestamp
          }
        }),
        widget_context: (data) => ({
          schema: getSchemaUrl('widget_context', 'dev'),
          data: {
            name: data.consumerName,
            title: data.widgetPageTitle,
            language: data.language ?? "",
            page_id: data.widgetPageId,
            page_type: data.pageType,
            search_terms: data.searchTerms,
            search_facets: data.searchFacets,
            cdbid: data.cdbid ?? "",
            schema_version: data.schemaVersion
          }
        }),
        impressions: (data) => ({
          schema: getSchemaUrl('impressions', 'dev'),
          data: {
            event_impressions: data.eventImpressions
          }
        }),
        app_env: (data) => ({
          schema: getSchemaUrl('app_env', 'dev'),
          data: {
            environment: data.environment,
            version: data.version
          }
        })
      },
      test: {
        // Test environment uses same builders as dev
        ...EVENT_BUILDERS.dev
      },
      prod: {
        page_unload: (data) => ({
          schema: getSchemaUrl('page_unload', 'prod'),
          data: {
            active_seconds: data.activeSeconds
          }
        }),
        button_click: (data) => ({
          schema: getSchemaUrl('button_click', 'prod'),
          data: {
            button_name: data.buttonName
          }
        }),
        widget_context: (data) => ({
          schema: getSchemaUrl('widget_context', 'prod'),
          data: {
            name: data.consumerName,
            title: data.widgetPageTitle,
            language: data.language ?? "",
            page_id: data.widgetPageId,
            page_type: data.pageType,
            search_terms: data.searchTerms,
            search_facets: data.searchFacets,
            cdbid: data.cdbid ?? ""
          }
        }),
        impressions: (data) => ({
          schema: getSchemaUrl('impressions', 'prod'),
          data: {
            event_impressions: data.eventImpressions
          }
        }),
        app_env: (data) => ({
          schema: getSchemaUrl('app_env', 'prod'),
          data: {
            environment: data.environment
          }
        })
      }
    };

    const buildEventData = (eventType, ...args) => {
      const collectedData = EVENT_DATA_COLLECTORS[environment][eventType](...args);
      return EVENT_BUILDERS[environment][eventType](collectedData);
    };

    const GLOBAL_WIDGET_CONTEXT = buildEventData('widget_context');
    const GLOBAL_ENVIRONMENT_CONTEXT = buildEventData('app_env');

    const getTrackerNames = () => {
      return [`widgets-tracker-${environment}`];
    };

    const trackerNames = getTrackerNames();

    window.widgetSnowplow("addGlobalContexts", [
      GLOBAL_WIDGET_CONTEXT,
      GLOBAL_ENVIRONMENT_CONTEXT,
    ], trackerNames);

    window.widgetSnowplow("trackPageView", trackerNames);

    window.widgetSnowplow("enableLinkClickTracking", trackerNames);

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

          window.widgetSnowplow(
            "trackSelfDescribingEvent", 
            buildEventData('button_click', category, label, action),
            trackerNames
          );
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
      trackViewedEventTeasers();
    });

    window.addEventListener("beforeunload", () => {
      window.widgetSnowplow(
        "trackSelfDescribingEvent", 
        buildEventData('page_unload'),
        trackerNames
      );

      window.widgetSnowplow(
        "trackSelfDescribingEvent", 
        buildEventData('impressions'),
        trackerNames
      );
    });

    const getSchemaUrl = (eventType) => {
      const schema = SCHEMA_CONFIG[snowplowBackendEnvironment][eventType];
      return `iglu:${schema.name}/jsonschema/${schema.version}`;
    };
  };
})(CultuurnetWidgets);
