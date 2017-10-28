function cnDrupalAutocompleteSourceBridge(request, response, url) {
  // change the regular autocomplete URL to its JSONP counterpart
 // jsonp_url = url.replace(/(https?:\/\/)([^\/]+\/)/, '$1$2autocomplete/jsonp/');
	// TODO above code did not work because our server is located in a subdir
	// somehow the base urls of autocompletion urls should be configurable, e.g.
	// http://uitwidgets.statiklabs.be/conf
	// so we just need to insert the jsonp counterpart path between the base url and
	// the remainder
  var drupal_ac_url = url + '/' + encodeURIComponent(request.term) + '?callback=?';
  
  cnJQuery.getJSON(drupal_ac_url, null, function(data) {
    ac_data = [];
    var i = 0;
    for (var key in data) {
      ac_data[i] = {"value": key, "label": data[key]};
      i++;
    }
    
    response(ac_data);
  });
}

cn.behaviors.autocomplete = {
  attach: function(context) {
    cnJQuery('input.form-autocomplete:not(.cn-autocomplete-processed)').each(function() {
      var ac_url_input_id = '#' + this.id + '-autocomplete';
      var url = cnJQuery(ac_url_input_id).val();
      
      var wrapper = cnJQuery('<div class="cn-autocomplete-wrapper"></div>').insertBefore(ac_url_input_id);
      
      cnJQuery(this).autocomplete({
        source: function(request, response) { cnDrupalAutocompleteSourceBridge(request, response, url); },
        appendTo: wrapper,
        // TODO: add throbber when autocompleting, check how Drupal does this exactly
        search: function(event, ui) {/*this.addClass('throbbing');*/}
      });
    }).addClass('cn-autocomplete-processed');
  }
};