cn.behaviors.gtm_link_tracking = {
  attach: function(context) {
    // todo: prevent possible double registration by adding an additional class
    cnJQuery('a', context).each(function () {
      cnJQuery(this).mousedown(function (event) {

        var widget = cnJQuery(this).closest('.cultuurnet-widget[data-widget-type]');
        if (widget.length == 0) {
          return;
        }

        var category;
        var widget_type = widget.attr('data-widget-type');
        if (widget_type.match(/searchresult/i) ) {
          if (widget.find('.push-events').length) {
            category = 'list';
          }
          else {
            category = 'detail';
          }
        }
        else if (widget_type.match(/searchbox/i)) {
          category = 'search';
        }
        else if (widget_type.match(/pushwidget/i)) {
          if (widget.find('.push-event-carousel').length) {
            category = 'push-carousel';
          }
          else if (widget.find('.push-event-list').length) {
            category = 'push-list';
          }
          else {
            category = 'push';
          }
        }
        else {
          category = widget_type;
        }

        var action_element = cnJQuery(this).closest('[data-event-action]');
        if (action_element.length == 0) {
          return;
        }

        event.preventDefault(); // don't open the link yet

        var action = action_element.attr('data-event-action');

        var gtm_data = {
          'event': 'trackEvent',
          'event-action': action.toLowerCase(),
          'event-category': category.toLowerCase()
        };

        var label = cnJQuery(this).attr('data-event-label');
        if (label) {
          gtm_data['event-label'] = label.toLowerCase();
        }

        var href = cnJQuery(this).attr('href');
        var target = cnJQuery(this).attr('target');

        cnWidgetsDataLayer.push(gtm_data);

        // Now wait 1 second and then mimic the default action.
        var timeout = 1000;
        setTimeout(function() {
          if (!target) {
            target = '_self';
          }
          //window.open(href, target);
        }, timeout);
      });
    });
  }
}
