cn.behaviors.cultuurnet_link_tracking = {
  attach: function(context) {
    cnJQuery('div.cultuurnet-layout a[data-cultuurnet-tracking-activity]:not(.cultuurnet-link-tracking-processed)').click(function() {
      var partnerKey = cnJQuery(this).attr('data-cultuurnet-tracking-partner-key');
      var user = cnJQuery(this).attr('data-cultuurnet-tracking-user');
      var activity = cnJQuery(this).attr('data-cultuurnet-tracking-activity');
      var params = JSON.parse(cnJQuery(this).attr('data-cultuurnet-tracking-params'));
      cultuurnet.track(partnerKey, user, activity, params);
    }).addClass('cultuurnet-link-tracking-processed');
  },
  
  detach: function(context) {
  
  }
};