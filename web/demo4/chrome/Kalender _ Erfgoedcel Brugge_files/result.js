/**
 * Initializes the result of the search result widget
 * @param integer widgetId Id of the widget
 * @return null
 */
function cnInitializeSearchResult(widgetId) {
  var cdbid = cnJQuery.url.param('cdbid');
  var method;

  var args = {};

  if (cdbid) {
    method = 'loadDetail';
    args.cdbid = cdbid;
  }
  else {
    method = 'getSearchResult';
    args.url = document.location.href;
  }

  cn.callWidget(widgetId, method, args).success(function(data) {
    var widget = cnJQuery('#cultuurnet-widget-' + widgetId);
    var control_inner_div = widget.find('div.cultuurnet-control-results > div.control-inside-container');
    control_inner_div.fadeOut('slow', function () {
      // Can't use cn.replaceHtml here as we want to have our fadeIn effect.
      cn.detachBehaviors(this);
      cnJQuery(this).html(data.value.html).fadeIn('slow');
      cn.attachBehaviors(this);
      
      if (typeof cultuurnet !== 'undefined' && typeof data.value !== 'undefined' && typeof data.value.tracking !== 'undefined') {
        cultuurnet.track(data.value.tracking.key, data.value.tracking.user, data.value.tracking.activity, data.value.tracking.params);
      }
      
      if (typeof data.value !== 'undefined' && typeof data.value.title !== 'undefined') {
        var title_meta = cnJQuery('meta[property="og:title"]');
        if (title_meta.length == 0) {
          title_meta = cnJQuery('<meta />').attr('property', 'og:title').attr('content', data.value.title).appendTo('head');
        }
        else {
          title_meta.attr('content', data.value.title);
        }
      }
    });

  });
}
