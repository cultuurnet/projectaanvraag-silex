function GET( name ) {
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return results[1];
}

cn.behaviors.event_details = {
    attach: function (context) {

        cnJQuery(".language-bar a").click(function(e){
            e.preventDefault();
            var l = cnJQuery(this).attr('href');
            cnJQuery(".push-event-detail").hide();
            cnJQuery(".push-event-detail[language='" + l + "']").show();
        });

        var language = GET('lng');
        if(!language && GET('cnlng')) {
            language = GET('cnlng');
        }
        else
        {
            language = 'nl';
        }
        cnJQuery(".language-bar a[href='" + language + "']").trigger('click');

    }
}


