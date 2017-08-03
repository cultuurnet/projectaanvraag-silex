// Extension of JQuery to retrieve the request parameters
(function (cnJQuery) { 
	cnJQuery.url = {}; 
	
	cnJQuery.extend(
		cnJQuery.url, { 
			_params: {}, 
			init: function() { 
				var paramsRaw = ""; 
				try { 
					paramsRaw = (document.location.href.split("?", 2)[1] || "").split("#")[0].split("&") || []; 
					for (var i = 0; i< paramsRaw.length; i++) { 
						var single = paramsRaw[i].split("=");
						if (single[0]) {
							single[1] = single[1].replace('+', ' ');

							this._params[single[0]] = unescape(single[1]);
						} 
					} 
				} catch(exception) { 
					//alert(exception); 
				} 
			}, 
			param: function(name) { 
				return this._params[name] || ""; 
			}, 
			paramAll: function() { 
				return this._params; 
			}
		}
	); 
	cnJQuery.url.init(); 
})(cnJQuery);