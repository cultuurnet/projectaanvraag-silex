cn.behaviors.placeholder = {
  attach: function (context) {
    cnJQuery('input', context).each(function() {
      try {
        cnJQuery(this).placeholder();
      }
      catch (e) {

      }
    });
  },
  detach: function (context) {

  }
};