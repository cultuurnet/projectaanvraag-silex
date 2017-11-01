if (typeof cn === 'undefined') {
	cn = { 'behaviors': {} };
	
	/* JS handling methods */
	
	cn.evalJs = function(js) {
	  var cnScript;
	  while (js.length > 0) {
		  cnScript = js.shift();
		  eval(cnScript);
	  }
	}
	
	/* CSS handling methods */
	
	/**
	 * Adds the provided array of CSS to the head.
	 */
	cn.addCss = function(css) {
	  while (css.length > 0) {
		  css = css.shift();
		  this.addStyle(css);
	  }
	}
	
  /**
   * Adds the provided inline CSS to the head
   * @param string css CSS script to add inline
   * @return null
   */
  cn.addStyle = function(css) {
    head = document.getElementsByTagName('head')[0];
    var styleElement = document.createElement("style");
    styleElement.setAttribute("type", "text/css");
    if (styleElement.styleSheet) { 
      styleElement.styleSheet.cssText = css; 
    } else { 
      var styleTextElement = document.createTextNode(css); 
      styleElement.appendChild(styleTextElement); 
    }
    head.appendChild(styleElement);
  };
  
  cn.includeCss = function(src) {
    var head = document.getElementsByTagName("head")[0];
      var linkElement = document.createElement("link");
      linkElement.setAttribute("type", "text/css");
      linkElement.setAttribute("rel", "stylesheet");
      linkElement.setAttribute("href", src);
      head.appendChild(linkElement);
  };

  /* Behavior handling methods */
  
  /**
   * Attach behaviors to a part of the page.
   */
	cn.attachBehaviors = function(context) {
	  context = context || document;
	  for (var behavior in cn.behaviors) {
		  if (cnJQuery.isFunction(cn.behaviors[behavior].attach)) {
			  cn.behaviors[behavior].attach(context);
		  }
	  }
	};

	/**
	 * Detach behaviors from a part of the page.
	 */
	cn.detachBehaviors = function(context, trigger) {
	  context = context || document;
	  trigger = trigger || 'unload';
	  
	  for (var behavior in cn.behaviors) {
		  if (cnJQuery.isFunction(cn.behaviors[behavior].detach)) {
			  cn.behaviors[behavior].detach(context, trigger);
		  }
	  }
	};
	
	/**
	 * Detaches any behaviors from an element,
	 * replaces the inner HTML of the element, and attaches behaviors again.
	 */
  cn.replaceHtml = function(element, html) {
    cn.detachBehaviors(element);
    element.html(html);
    cn.attachBehaviors(element);
  }
	
	/* Widget methods */
	
  /**
   * Calls the widget server with the provided url
   * @param string url Url to invoke
   * @param params callback Callback on success
   * @return null
   */
  cn.call = function(url, params) {
    params = params || {};

    if (typeof params.dataType == 'undefined') {
      params.dataType = 'jsonp';
    }

    if (params.dataType == 'jsonp') {
      params.crossDomain = true;
    }
    
    // TODO need to return our own Deferred here
    // (see http://stackoverflow.com/questions/5111695/jquery-jqxhr-cancel-chained-calls-trigger-error-chain)
    // and make it fail when data.code does not equal 0
    return cnJQuery.ajax(url, params).success(function(data) {
      if (data.code != 0) {
        // TODO: improve error handling, make it grandmother-proof
        var message = 'Error code ' + data.code + "\n";
        
        for (var key = 0; key < data.messages.length; key++) {
          message += "* " + data.messages[key] + "\n";
        }
        
        alert(message);
      } else {
        if (data.messages && typeof console != 'undefined' && console.log != "undefined") {
          for (var key = 0; key < data.messages.length; key++) {
            console.log(data.messages[key]);
          }
        }
      }
    });
  };

	/**
	 * Calls a method on a widget
	 * @param integer widgetId Id of the widget
	 * @param string methodName Name of the method on the widget
	 * @param json args JSON object acting as a argument container
	 * @return null
	 */
	cn.callWidget = function(widgetId, methodName, args, params) {
	  params = params || {};
	  args = args || {};
		var url = cnConfig.callUrl + encodeURIComponent(widgetId) + '/' + encodeURIComponent(methodName);

		// use JSONP (callback=?) to bypass the same origin policy
		url = url + '?' + cnJQuery.param(args) + '&callback=?';

        var call = this.call(url, params);
        call.success(function(data) {
          if (data.value.gtm && cnWidgetsDataLayer) {
            cnWidgetsDataLayer.push(data.value.gtm);
          }
        });

        return call;
	};

	/**
	 * Rerenders the provided widget
	 * @param integer widgetId Id of the widget
	 * @return null
	 */
	cn.renderWidget = function(widgetId) {
		var widget = cnJQuery("#cultuurnet-widget-" + widgetId);
		var widget_inner = widget.find('div.widget-inside-container');
		
		var url = cnConfig.renderUrl + encodeURIComponent(widgetId);
		
		var behaviors_target = widget_inner.length > 0 ? widget_inner : widget;
	
		return this.call(url, {dataType: 'json'}).success(function(data) {
		  if (widget_inner.length == 0) {
			  widget.html('<div class="widget-inside-container"></div>');
			  widget_inner = widget.find('div.widget-inside-container');
		  }
		  
		  cn.detachBehaviors(behaviors_target);
			
			var el_id = "#cultuurnet-widget-" + widgetId + ' div.widget-inside-container';
			var rule = cnJQuery.rule(widget);
			
			rule.sheets.each(function(index, sheet) {
			  
			  var rules;
			  
			  // the try catch block is here to avoid
			  // security errors on firefox
			  // as it does not allow to access cssRules on
			  // external stylesheets
			  try {
  			  if ('cssRules' in this && this.cssRules) {
  			    rules = this.cssRules;
  			  }
  			  else if ('rules' in this && this.rules) {
  			    rules = this.rules;
  			  }
			  } catch (err) {
			    
			  }
			  
			  if (!rules) {
			    return true;
			  }
			  
			  // unable to use cnJQuery.each() as it does not support reverse
			  // and we need reverse because we are removing items from the css rules list
			  
			  for (var i = rules.length - 1; i >= 0; --i) {
			    if (!('selectorText' in rules[i])) {
            continue;
          }

          if (rules[i].selectorText.match('^' + el_id)) {
         
            if (typeof sheet.deleteRule == 'function') {
              sheet.deleteRule(i);
            }
            else if (typeof sheet.removeRule == 'function') {
              sheet.removeRule(i);
            }
          }
			  }
			});
			
			widget_inner.html(data.value.html);
			
			if (data.value.css) {
			  cn.addStyle(data.value.css);
			}

			var chain;
			
			if (data.value.js_includes) {
			  for (i = 0; i < data.value.js_includes.length; i++) {
			    if (typeof chain != 'undefined') {
			      chain.then(data.value.js_includes[i]);
			    }
			    else {
			      chain = load(data.value.js_includes[i]);
			    }
			  }
			}
			
			if (data.value.js) {
			  if (typeof chain != 'undefined') {
          chain.thenRun(function() { eval(data.value.js); });
			  }
			  else {
			    eval(data.value.js);
			  }
			}

			cn.attachBehaviors(behaviors_target);
		});
	};
	
	/* Utility functions useful for development. */
	
	cn.log = function(message) {
		if (typeof console != 'undefined' && typeof console.log != 'undefined') {
			console.log(message);
		}
	};
}