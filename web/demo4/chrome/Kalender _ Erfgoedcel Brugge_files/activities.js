cn.behaviors.activities = {
  attach: function (context) {
    cnJQuery('.activity-link', context).each(function() {
      cnJQuery(this).click(function() {
        var link = cnJQuery(this);

        var widget = link.closest('div[data-widget-id]');
        var widget_id = widget.attr('data-widget-id');
        
        var activity_type = link.attr('data-activity-type');
        var action = link.attr('data-activity-action');
        
        var event_cdbid = link.attr('data-event-cdbid');
        var event_title = link.attr('data-event-title');
        
        // Descend up the DOM if the activity link itself does not contain a cdbid.
        if (!event_cdbid) {
          event_element = link.closest('[data-event-cdbid]');
          if (event) {
            event_cdbid = cnJQuery(event_element).attr('data-event-cdbid');
            event_title = cnJQuery(event_element).attr('data-event-title');
          }
        }
        
        if (action === 'create') {
          var args = {'type': activity_type, 'event_cdbid': event_cdbid, 'event_title': event_title};

          cn.callWidget(widget_id, 'createActivity', args).success(function(data) {
            // The backend can instruct to track this request with the tracking API.
            if (typeof data.value != 'undefined' && typeof data.value.tracking != 'undefined') {
              cultuurnet.track(data.value.tracking.key, data.value.tracking.user, data.value.tracking.activity, data.value.tracking.params);
            }
            
            if (typeof data.value !== 'undefined' && typeof data.value.activity_id !== undefined) {
              // Social network sharing activities can not be toggled so keep those links like they currently are.
              if (activity_type != 6 && activity_type != 7) {
                cnJQuery('.activity-link[data-event-cdbid="' + event_cdbid + '"][data-activity-type="' + activity_type + '"]').each(function () {
                  
                  var new_action = 'remove'
                    
                  var link_mode = cnJQuery(this).attr('data-activity-link-mode');

                  cnJQuery(this).attr('data-activity-action', new_action);
                  cnJQuery(this).attr('data-activity-id', data.value.activity_id);
                  
                  cnJQuery(this).addClass('activity-' + new_action);
                  cnJQuery(this).removeClass('activity-' + action);
                  
                  var new_title = cnJQuery(this).attr('data-activity-action-' + new_action + '-title');
    
                  if (new_title) {
                    cnJQuery(this).attr('title', new_title);
                    if (link_mode == 'full') {
                      cnJQuery(this).html(new_title);
                    }
                  }
                });
              }  
            }
            
            // Trigger a custom event culturefeed-activity on the document.
            var activity_event = cnJQuery.Event('culturefeedActivity');
            activity_event.activity_action = action;
            activity_event.activity_type = activity_type;
            activity_event.event_cdbid = event_cdbid;
            cnJQuery(document).trigger(activity_event);
          });

          // For social network sharing activities we continue with the regular link event handling flow.
          if (activity_type == 6 || activity_type == 7) {
            return true;
          }
        }
        else {
          var id = link.attr('data-activity-id');
          var args = {'id': id};
          
          cn.callWidget(widget_id, 'removeActivity', args).success(function(data) {
            cnJQuery('.activity-link[data-event-cdbid="' + event_cdbid + '"][data-activity-type="' + activity_type + '"]').each(function () {
              var new_action = 'create';

              var link_mode = cnJQuery(this).attr('data-activity-link-mode');
              
              cnJQuery(this).attr('data-activity-action', new_action);
              cnJQuery(this).removeAttr('data-activity-id');

              cnJQuery(this).addClass('activity-' + new_action);
              cnJQuery(this).removeClass('activity-' + action);

              var new_title = cnJQuery(this).attr('data-activity-action-' + new_action + '-title');

              if (new_title) {
                cnJQuery(this).attr('title', new_title);
                if (link_mode == 'full') {
                  cnJQuery(this).html(new_title);
                }
              }
            });
            
            // Trigger a custom event culturefeed-activity on the document.
            var activity_event = cnJQuery.Event('culturefeedActivity');
            activity_event.activity_action = action;
            activity_event.activity_type = activity_type;
            activity_event.activity_id = id;
            activity_event.event_cdbid = event_cdbid;
            cnJQuery(document).trigger(activity_event);
          });
        }

        return false;
      });
    });
    
    cnJQuery('.cn-refinement-where').each(function() {
      var items = cnJQuery('li', this);
      if(items.length >= 15) {
        items.slice(10).hide();
        var meer = cnJQuery('<a class="show-more" href="#">Meer</a>');
        meer.insertAfter(this);
        meer.wrap('<p />');
        meer.bind('click.activities', function() {
          items.slice(10).slideToggle();  
          meer.hide();
        });
      }
    });
  },

  detach: function (context) {
    cnJQuery('.activity-link', context).unbind('click');
  }
}