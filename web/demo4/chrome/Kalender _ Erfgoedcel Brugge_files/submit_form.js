cn.behaviors.submit_form = {
  attach: function (context) {
    cnJQuery('div.submit-with-js input[type="text"]').keydown(function (e) {
        
      // catch enter keypresses on the input fields, and have them click the button:
      if (e.keyCode == 13) {
        cnJQuery('div.submit-with-js input.submit').click();
        e.preventDefault();
      }
     });

    cnJQuery('div.submit-with-js input.submit', context).click(function(e) {
      var context = cnJQuery(this).closest('div.cultuurnet-widget');

      var url = cnJQuery('div.attr-submit-with-js input[name=cn_action]', context).attr('value');
      var new_window = cnJQuery('div.attr-submit-with-js input[name=cn_new_window]', context).attr('value');

      var values = cnJQuery('div.submit-with-js input, div.submit-with-js select', context).serialize();

      if (!url) {
        url = "" + window.location;
        var position = url.indexOf('?');
        if (position == -1) {
          url += '?';
        } else {
          url = url.substring(0, position + 1);
        }
      } else {
        var position = url.indexOf('?');
        if (position == -1) {
          url += '?';
        }
      }

      if (new_window) {
        window.open(url + values, '', 'toolbar=yes,location=yes,directories=yes,status=yes,menubar=yes,scrollbars=yes,copyhistory=yes,resizable=yes');
      } else {
        window.location = url + values;
      }

      return false;
    });
  },
  detach: function (context) {

  }
};
