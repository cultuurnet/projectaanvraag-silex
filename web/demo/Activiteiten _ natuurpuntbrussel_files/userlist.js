cnJQuery(document).ready(function () {
  cnJQuery(document).bind('culturefeedActivity', function(event) {
    cnJQuery('.cn-user-list').each(function() {
      var user_list = cnJQuery(this);
      
      var widget = user_list.closest('div[data-widget-id]');
      var widget_id = widget.attr('data-widget-id');
      
      var activity_type = user_list.attr('data-activity-type');
      var event_cdbid = user_list.attr('data-event-cdbid');

      if (event.activity_type == activity_type && event.event_cdbid == event_cdbid) {
        args = {'event_cdbid': event_cdbid, 'activity_type': activity_type};
        // @todo Show some kind of indicator that the list is refreshing.
        cn.callWidget(widget_id, 'userList', args).success(function(data) {
          cn.replaceHtml(user_list, data.value);
        });
      }
    });
  });
});
