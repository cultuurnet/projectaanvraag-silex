cn.behaviors.login_popup = {
  attach: function (context) {
    cnJQuery('.uitid-popup', context).click(function() {
      var loginWindow = window.open(cnJQuery(this).attr('href'), '', 'toolbar=0,location=0,menuBar=0,width=800,height=500,top=250');
      var watchClose = setInterval(function() {
      if (loginWindow.closed) {
        clearTimeout(watchClose);
        location.reload();
      }
      }, 200);
    
      return false;
    });
  },
  
  detach: function (context) {
    cnJQuery('.uitid-popup', context).unbind('click');
  }
}