cn.behaviors.datepicker = {
  attach: function (context) {
    cnJQuery('input.date-date:not(.datepicker-processed)', context).each(function() {
      cnJQuery(this).datepicker({dateFormat: 'd/mm/yy'});
    }).addClass('datepicker-processed');
  },
  detach: function (context) {

  }
};