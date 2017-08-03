cn.behaviors.show_tooltiptext = {
  attach: function(context) {
    /* jquery hover effect detail view */
    cnJQuery(".tooltip").hover(function(e){
      cnJQuery(this).addClass('hover');
      cnJQuery(this).next(".tooltip-text").show();
    },
    function() {
      cnJQuery(this).removeClass('hover');
      cnJQuery(this).next(".tooltip-text").hide();
    });
    cnJQuery(".tooltip").trigger('hover');
    
    
    /* jquery hover effect list view */
    cnJQuery(".tooltip-list").hover(function(e){
	  cnJQuery(this).addClass('hover');
      cnJQuery(this).next(".tooltip-list-text").show();
    },
    function() {
      cnJQuery(this).removeClass('hover');
      cnJQuery(this).next(".tooltip-list-text").hide();
    });
    cnJQuery(".tooltip-list").trigger('hover');
  }
}